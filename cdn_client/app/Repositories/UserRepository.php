<?php

namespace App\Repositories;

use App\Dto\InputPageDto;
use App\Dto\InputUserDto;
use App\Enums\ListType;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserRepository extends BaseRepository
{
    protected $user;

    const HASH_OPTION = ['rounds' => 12];

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function createUser(InputUserDto $userDto)
    {
        $this->user->name = $userDto->getName();
        $this->user->email = $userDto->getEmail();
        $this->user->password = $this->getPasswordHash($userDto->getPassword());
        $this->user->status = $userDto->getStatus();
        $this->user->user_type = $userDto->getUserType();
        $this->user->remark = $userDto->getRemark();
        $this->user->save();
    }

    public function getUserById(int $id)
    {
        return  $this->user->find($id);
    }

    public function getUserListByPage(InputPageDto $inPageManagement, ListType $type)
    {
        $Page = $inPageManagement->getPage();
        $Limit = $inPageManagement->getLimit();
        $Sort = $inPageManagement->getSort();
        $SortColumn = $inPageManagement->getSortColumn();
        $Search = $inPageManagement->getSearch();

        //搜尋項目
        $userOrm = $this->user->select("id", "name", "email", "status", "user_type", "login_ip", "login_time", "created_at", "updated_at");

        //where 條件
        $userTypeWhere = array("name" => "name", "email" => "email", "status" => "status", "userType" => "user_type");
        $userOrm = (isset($Search["status"])) ? $userOrm->where($userTypeWhere["status"], $Search["status"]) : $userOrm;
        $userOrm = (isset($Search["name"])) ? $userOrm->where($userTypeWhere["name"], "like", $this->stringMixLike($Search["name"])) : $userOrm;
        $userOrm = (isset($Search["email"])) ? $userOrm->where($userTypeWhere["email"], "like", $this->stringMixLike($Search["email"])) : $userOrm;
        $userOrm = (isset($Search["user_type"])) ? $userOrm->where($userTypeWhere["user_type"], "like", $this->stringMixLike($Search["user_type"])) : $userOrm;

        //判斷是否取總數
        $isGetListCount = $this->isGetListCount($type);
        if ($isGetListCount) {
            return $userOrm->select("id")->count();
        }

        //排序
        $orderByColums = array_merge($userTypeWhere, array("loginIp" => "login_ip", "loginTime" => "login_time", "createdAt" => "created_at", "updatedAt" => "updated_at"));
        if ($Sort != "" && $SortColumn != "" && in_array($SortColumn, array_keys($orderByColums))) {
            $userOrm = $userOrm->orderBy($orderByColums[$SortColumn], $Sort);
        } else {
            $userOrm = $userOrm->orderBy("id", $Sort);
        }

        //筆數
        $userOrm = $userOrm->offset(($Page - 1) * $Limit);
        $userOrm = $userOrm->limit($Limit);

        return $userOrm->get();
    }

    public function deleteUserById($id)
    {
        $this->user->destroy($id);
    }

    private function getPasswordHash(string $passowrd)
    {
        return Hash::make($passowrd, $this::HASH_OPTION);
    }

    private function validPassword(string $passowrd, string $hashedPassword)
    {
        return Hash::check($passowrd, $hashedPassword, $this::HASH_OPTION);
    }
}
