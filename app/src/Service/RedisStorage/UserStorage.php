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
        $this->redis->set(sprintf("user:%s", $user->getId()), json_encode($user->toArray(), JSON_THROW_ON_ERROR));
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

        /** @phpstan-ignore-next-line  */
        $userDecode = json_decode($report, true, JSON_THROW_ON_ERROR);

        if (!is_array($userDecode)) {
            throw new \Exception(sprintf('Invalid user data for id %s', $id));
        }

        /** @phpstan-ignore-next-line  */
        return UserFactory::fromArray($userDecode);
    }

    /**
     *
     * @param int $page
     * @param int $perPage
     *
     * @return array<string, mixed>
     * @throws RedisException
     */
    public function getAllUsersPaginated(int $page = 1, int $perPage = 10): array
    {
        $userIds = $this->redis->sMembers("users:list");
        $totalUsers = count($userIds);
        $totalPages = (int) ceil($totalUsers / $perPage);

        $pagedUserIds = array_slice($userIds, ($page - 1) * $perPage, $perPage);
        $users = [];

        foreach ($pagedUserIds as $id) {
            try {
                $users[] = $this->get($id);
            } catch (\Exception $e) {
                continue;
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