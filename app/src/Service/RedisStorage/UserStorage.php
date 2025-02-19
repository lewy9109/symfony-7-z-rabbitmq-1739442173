<?php

namespace App\Service\RedisStorage;

use App\Service\User\UserDto;
use App\Service\User\UserFactory;
use RedisException;

class UserStorage extends Storage
{
    /**
     * @param UserDto $user
     *
     * @return void
     * @throws RedisException
     */
    public function save(UserDto $user): void
    {
        $this->redis->set(sprintf("user:%s", $user->getId()), json_encode($user->toArray()));
        $this->redis->sAdd("users:list", $user->getId());
    }

    /**
     * @throws RedisException
     * @throws \Exception
     */
    public function get(string $id): ?UserDto
    {
        $report = $this->redis->get("user:$id");

        if(!$report){
            throw new \Exception(sprintf('User with id %s not found', $id));
        }

        $userDecode =  json_decode($report, true);

        return UserFactory::fromArray($userDecode);
    }

    /**
     * Get a paginated list of users stored in Redis
     *
     * @param int $page Current page number (starts at 1)
     * @param int $perPage Number of users per page
     *
     * @return array ['users' => UserDto[], 'totalPages' => int, 'totalUsers' => int]
     * @throws RedisException
     */
    public function getAllUsersPaginated(int $page = 1, int $perPage = 10): array
    {
        $userIds = $this->redis->sMembers("users:list");
        $totalUsers = count($userIds);
        $totalPages = (int) ceil($totalUsers / $perPage);

        // Get user IDs for the requested page
        $pagedUserIds = array_slice($userIds, ($page - 1) * $perPage, $perPage);
        $users = [];

        foreach ($pagedUserIds as $id) {
            try {
                $users[] = $this->get($id);
            } catch (\Exception $e) {
                continue; // Skip missing users
            }
        }

        return [
            'users' => $users,
            'totalPages' => $totalPages,
            'totalUsers' => $totalUsers,
            'currentPage' => $page
        ];
    }
}