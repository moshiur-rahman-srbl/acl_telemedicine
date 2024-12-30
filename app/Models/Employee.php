<?php

namespace App\Models;

use common\integration\Utility\Arr;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $guarded = [];
    const MOVE_TO_TRASH = "move_to_trash";

    const MALE = 1;
    const FEMALE = 2;

    public function getGender()
    {
        return [
            self::MALE => "Male",
            self::FEMALE => "Female"
        ];
    }

    public function getAll($filters = [], $paginate = false, $page_limit = 10)
    {
        $query = $this->filters($filters);
        return $paginate ? $query->paginate($page_limit) : $query->get();
    }

    public function saveData($input)
    {
        return self::query()->create($input);
    }

    public function findById($id)
    {
        return self::query()->where("id", $id)->first();
    }

    public function updateData($id, $input)
    {
        return self::query()
            ->where("id", $id)
            ->update($input);
    }

    public function deleteData($ids)
    {
        $query = self::query();
        if (Arr::isOfType($ids)) {
            $query->whereIn("id", $ids);
        } else {
            $query->where("id", $ids);
        }
        return $query->delete();
    }

    private function filters($filters = [])
    {
        $query = self::query();

        if (!empty($filters["name"])) {
            $query->where("name", $filters["name"]);
        }

        if (!empty($filters["description"])) {
            $query->where("description", $filters["description"]);
        }

        if (!empty($filters["filter_key"])) {
            $like_operator = "like";
            $query->where(function ($q) use ($filters, $like_operator) {
                $q->where('name', $like_operator, '%' . $filters['filter_key'] . '%')
                    ->orWhere('id', $like_operator, '%' . $filters["filter_key"] . '%')
                    ->orWhere('description', $like_operator, '%' . $filters["filter_key"] . '%');
            });
        }

        return $query;
    }

    public function handleSearch($input): array
    {
        $filters["page_limit"] = $input["page_limit"] ?? 10;
        $filters["filter_key"] = $input["filter_key"] ?? '';
        $filters["name"] = $input["name"] ?? '';
        $filters["description"] = $input["description"] ?? '';
        return $filters;
    }

    public function prepareInsertData($input): array
    {
        $store_data["name"] = $input["name"];
        $store_data["phone"] = $input["phone"];
        $store_data["education"] = $input["education"];
        $store_data["designation"] = $input["designation"];
        $store_data["gender"] = $input["gender"];
        $store_data["email"] = $input["email"];
        $store_data["address"] = $input["address"];
        return $store_data;
    }

    public function validateData(): array
    {
        $rules = [
            "name" => "required|string|max:50",
        ];
        $messages = [
            'name.required' => __("Name is required"),
        ];
        return [$rules, $messages];
    }
}
