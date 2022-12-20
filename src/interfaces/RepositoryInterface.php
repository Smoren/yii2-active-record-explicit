<?php

namespace Smoren\Yii2\ActiveRecordExplicit\interfaces;

use Smoren\Yii2\ActiveRecordExplicit\models\ActiveRecord;

interface RepositoryInterface
{
    /**
     * @return ActiveRecord::class
     */
    public function getModelClass(): string;

    /**
     * @return array<ActiveRecord::class>
     */
    public function getRelatedModelClasses(): array;
}
