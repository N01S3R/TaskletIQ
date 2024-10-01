<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Token;
use Doctrine\ORM\EntityRepository;

class TokenRepository extends EntityRepository
{
    /**
     * Zwraca liczbę tokenów użytkownika.
     *
     * @param int $userId
     * @return int
     */
    public function getTokenCountByUserId(int $userId): int
    {
        return (int) $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->where('t.user = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Tworzy nowy token dla użytkownika.
     *
     * @param User $user
     * @param string $tokenValue
     * @return Token
     */
    public function createTokenForUser(User $user, string $tokenValue): Token
    {
        $token = new Token();
        $token->setToken($tokenValue);
        $token->setUser($user);
        $token->setExpiration((new \DateTime())->modify('+1 day'));

        $this->_em->persist($token);
        $this->_em->flush();

        return $token;
    }

    /**
     * Zwraca tokeny i ich daty wygaśnięcia powiązane z danym użytkownikiem jako tablicę.
     *
     * @param int $userId Identyfikator użytkownika
     * @return array Tablica zawierająca tokeny i daty wygaśnięcia
     */
    public function getTokensByUserId(int $userId): array
    {
        return $this->createQueryBuilder('t')
            ->select('t.id, t.token, t.expiration')
            ->where('t.user = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * Usuwa dany token i zapisuje zmiany w bazie danych.
     *
     * @param Token $token
     */
    public function remove(Token $token): void
    {
        $this->_em->remove($token);
        $this->_em->flush();
    }
}
