<?php


namespace Smoren\Yii2\ActiveRecordExplicit\models;


use DateTime;
use Smoren\ExtendedExceptions\BadDataException;
use Smoren\Yii2\ActiveRecordExplicit\exceptions\DbException;
use Smoren\Yii2\ActiveRecordExplicit\wrappers\WrappableInterface;
use Throwable;
use yii\behaviors\TimestampBehavior;
use yii\helpers\ArrayHelper;

/**
 * Класс для AR модели текущего приложения
 */
abstract class ActiveRecord extends \yii\db\ActiveRecord implements WrappableInterface
{
    protected $hasDirtyFieldsToUpdate = false;

    public function behaviors(): array
    {
        return ArrayHelper::merge(
            parent::behaviors(),
            [
                'timestamp' => [
                    'class' => TimestampBehavior::class,
                    'createdAtAttribute' => $this->hasAttribute('created_at') ? 'created_at' : false,
                    'updatedAtAttribute' => $this->hasAttribute('updated_at') ? 'updated_at' : false,
                    'value' => (new DateTime())->getTimestamp(),
                ],
            ]
        );
    }

    /**
     * Опеределяет название формы
     */
    public function formName()
    {
        return '';
    }

    /**
     * Создает экземпляр запроса
     * @return ActiveQuery
     */
    public static function find()
    {
        return new ActiveQuery(get_called_class());
    }

    /**
     * Занимается вставкой записи в БД. А также может обновить существующую запись.
     * В случае неуспешной вставки или обновления будет выброшено исключение.
     * @param bool $runValidation
     * @param null $attributeNames
     * @return bool
     * @throws DbException
     */
    public function save($runValidation = true, $attributeNames = null)
    {
        // TODO применить needUpdate()?
        $errorMessage = 'cannot save instance';
        try {
            if(!($result = parent::save($runValidation, $attributeNames))) {
                throw new DbException($errorMessage, DbException::STATUS_CANNOT_SAVE_INSTANCE, null, $this->errors);
            }
        } catch(Throwable $e) {
            throw new DbException(
                $errorMessage, DbException::STATUS_UNKNOWN, $e, $this->errors, [
                    'message' => $e->getMessage(),
                ]
            );
        }
        return $result;
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
        $this->hasDirtyFieldsToUpdate = true;
        parent::__set($name, $value);
    }

    /**
     * Нужно ли обновить данные в БД
     * @return bool
     */
    public function needUpdate(): bool
    {
        return $this->hasDirtyFieldsToUpdate;
    }
}
