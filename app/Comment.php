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
        return $query->where(function ($query) use ($callable) {
            list($type, $key) = $this->getPolymorphicFields();

            $this->newQuery()->distinct()->get([$type])->keyBy($type)->map(function ($model) use ($type) {
                return (new $model->{$type})->getTable();
            })->each(function ($table, $modelClass) use ($query, $type, $key, $callable) {
                $query->orWhereExists(function ($query) use ($table, $modelClass, $type, $key, $callable) {
                    $model = new $modelClass;

                    $eloBuilder = clone $model->query();
                    $eloBuilder
                        ->where("{$this->getTable()}.{$type}", $modelClass)
                        ->whereRaw("{$this->getTable()}.{$key} = {$table}.{$model->getKeyName()}")
                        ->when($callable instanceof \Closure, function ($query) use ($callable, $model) {
                            $query->where($callable);
                        });

                    $query->selectRaw(
                        ltrim($eloBuilder->toSql(), 'select '),
                        $eloBuilder->getBindings()
                    );
                });
            });
        });
    }

    protected function getPolymorphicFields()
    {
        $relation = $this->commentable();

        return [$relation->getMorphType(), $relation->getForeignKey()];
    }
}
