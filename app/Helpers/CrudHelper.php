<?php

namespace App\Helpers;

use Illuminate\Database\Eloquent\Model;
use Exception;

class CrudHelper
{
    public static function create(Model $model, array $data)
    {
        try {
            return $model::create($data);
        } catch (Exception $e) {
            return null;
        }
    }

    public static function update(Model $model, array $data)
    {
        try {
            $model->update($data);
            return $model;
        } catch (Exception $e) {
            return null;
        }
    }

    public static function delete(Model $model)
    {
        try {
            $model->delete();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
