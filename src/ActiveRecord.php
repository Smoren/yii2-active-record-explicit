<?php


namespace Smoren\Yii2\ActiveRecordExplicit;


use yii\base\InvalidConfigException;

/**
 * Класс для AR модели текущего приложения
 */
abstract class ActiveRecord extends \yii\db\ActiveRecord
{

    protected $hasDirtyFieldsToUpdate = false;

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
        try {
            if(!($result = parent::save($runValidation, $attributeNames))) {
                throw new DbException('cannot save instance', DbException::STATUS_CANNOT_SAVE_INSTANCE, null, $this->errors);
            }
        } catch(\Throwable $e) {
            throw new DbException($e->getMessage(), DbException::STATUS_CANNOT_SAVE_INSTANCE, $e, $this->errors, ['message' => $e->getMessage()]);
        }
        return $result;
    }

    /**
     * Удаление записи из БД.
     * В случае невозможности удалить запись будет выброшено исключение.
     * @inheritdoc
     * @return false|int
     * @throws DbException
     */
    public function delete()
    {
        $errorMessage = 'cannot delete instance';
        try {
            if($this->isNewRecord) {
                throw new DbException($errorMessage, DbException::STATUS_LOGIC_ERROR);
            }
            return parent::delete();
        } catch(\Throwable $e) {
            throw new DbException($errorMessage, DbException::STATUS_CANNOT_DELETE_INSTANCE, $e, $this->errors, ['message' => $e->getMessage()]);
        }
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
     * Нужно ли обновить данные в БД
     * @return bool
     */
    public function needUpdate(): bool
    {
        return $this->hasDirtyFieldsToUpdate;
    }

    /**
     * @inheritDoc
     */
    public function __set($name, $value)
    {
        $this->hasDirtyFieldsToUpdate = true;
        parent::__set($name, $value);
    }
}