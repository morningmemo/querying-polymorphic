<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $fillable = [
        'body'
    ];

    public function commentable()
    {
        return $this->morphTo();
    }

    public function scopeWhereHasCommentable($query, $callable = null)
    {
        list($type, $key) = $this->getPolymorphicFields();

        $this->newQuery()->distinct()->get([$type])->keyBy($type)->map(function ($model) use ($type) {
            return (new $model->{$type})->getTable();
        })->each(function ($table, $modelClass) use (&$query, $type, $key, $callable) {
            $query = $query->orWhereExists(function ($query) use ($table, $modelClass, $type, $key, $callable) {
                $model = new $modelClass;

                $query->select('*')->from($table)->where("{$this->getTable()}.{$type}", $modelClass)
                    ->whereRaw("{$this->getTable()}.{$key} = {$table}.{$model->getKeyName()}")
                    ->when($callable instanceof \Closure, function ($query) use ($callable) {
                        $query->where($callable);
                    });
            });
        });

        return $query;
    }

    protected function getPolymorphicFields()
    {
        $relation = $this->commentable();

        return [$relation->getMorphType(), $relation->getForeignKey()];
    }
}
