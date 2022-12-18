<?php

namespace Smoren\Yii2\ActiveRecordExplicit\models;

use Smoren\ExtendedExceptions\BadDataException;
use Smoren\Yii2\ActiveRecordExplicit\behaviors\AttributeTypecastBehavior;
use Smoren\Yii2\ActiveRecordExplicit\behaviors\TimestampBehavior;
use Smoren\Yii2\ActiveRecordExplicit\exceptions\DbException;
use Smoren\Yii2\ActiveRecordExplicit\interfaces\DbConnectionManagerInterface;
use Smoren\Yii2\ActiveRecordExplicit\wrappers\WrappableInterface;
use yii\base\InvalidConfigException;
use yii\db\Connection;
use yii\di\NotInstantiableException;
use Yii;
use Throwable;

/**
 * Класс для AR модели текущего приложения
 */
abstract class ActiveRecord extends \yii\db\ActiveRecord implements WrappableInterface
{
    /**
     * @var bool
     */
    protected $hasDirtyFieldsToUpdate = false;

    /**
     * @var bool
     */
    protected $useTypecast = true;

    /**
     * @inheritDoc
     */
    public function behaviors(): array
    {
        $result = parent::behaviors();

        $result['timestamp'] = [
            'class' => TimestampBehavior::class,
        ];

        if($this->useTypecast) {
            $result['typecast'] = [
                'class' => AttributeTypecastBehavior::class,
                'typecastBeforeValidate' => true,
                'strict' => true,
            ];
        }

        return $result;
    }

    /**
     * Определяет название формы
     */
    public function formName(): string
    {
        return '';
    }

    /**
     * Занимается вставкой записи в БД. А также может обновить существующую запись.
     * В случае неуспешной вставки или обновления будет выброшено исключение.
     * @param bool $runValidation
     * @param null $attributeNames
     * @return bool
     * @throws DbException
     */
    public function save($runValidation = true, $attributeNames = null): bool
    {
        $errorMessage = 'cannot save instance';
        $tr = static::getDb()->beginTransaction();
        try {
            if(!($result = parent::save($runValidation, $attributeNames))) {
                throw new DbException(
                    $errorMessage, DbException::STATUS_CANNOT_SAVE_INSTANCE, null, $this->errors
                );
            }
            $tr->commit();
            $this->hasDirtyFieldsToUpdate = false;

            return $result;
        } catch(DbException $e) {
            $tr->rollBack();
            throw $e;
        } catch(Throwable $e) {
            $tr->rollBack();
            throw new DbException(
                $errorMessage, DbException::STATUS_UNKNOWN, $e, $this->errors, [
                    'message' => $e->getMessage(),
                ]
            );
        }
    }

    /**
     * Удаление записи из БД.
     * В случае невозможности удалить запись будет выброшено исключение.
     * @return false|int
     * @throws DbException
     */
    public function delete()
    {
        $errorMessage = 'cannot remove instance';
        try {
            if($this->isNewRecord) {
                throw new BadDataException($errorMessage, 1);
            }
            return parent::delete();
        } catch(Throwable $e) {
            throw new DbException(
                $errorMessage, DbException::STATUS_CANNOT_DELETE_INSTANCE, $e, $this->errors, [
                    'message' => $e->getMessage(),
                ]
            );
        }
    }

    /**
     * {@inheritDoc}
     */
    public function refresh()
    {
        try {
            return parent::refresh();
        } catch(DbException $e) {
            return false;
        }
    }

    /**
     * Поиск одной записи по составленному в $condition условию.
     * @param $condition
     * @return \yii\db\ActiveRecord|null
     */
    public static function findOne($condition)
    {
        return parent::findOne($condition);
    }

    /**
     * Выбирает только поля таблицы. Они же свойства модели ActiveRecord
     * @return array
     */
    public static function getColumns(): array
    {
        return array_keys((new static())->attributes);
    }

    /**
     * @inheritDoc
     */
    public function __set($name, $value)
    {
        parent::__set($name, $value);
        $this->hasDirtyFieldsToUpdate = true;
    }

    /**
     * Нужно ли обновить данные в БД
     * @return bool
     */
    public function needUpdate(): bool
    {
        return $this->hasDirtyFieldsToUpdate;
    }

    /**
     * {@inheritDoc}
     */
    public static function getDb(): Connection
    {
        if(($connectionManager = static::getConnectionManager()) !== null) {
            return $connectionManager->getConnection(static::class);
        }
        return parent::getDb();
    }

    /**
     * @return DbConnectionManagerInterface|null
     */
    public static function getConnectionManager(): ?DbConnectionManagerInterface
    {
        if(Yii::$container->has(DbConnectionManagerInterface::class)) {
            try {
                return Yii::$container->get(DbConnectionManagerInterface::class);
            } catch(InvalidConfigException|NotInstantiableException $e) {
                return null;
            }
        }

        return null;
    }
}
