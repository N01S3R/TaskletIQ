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

    /**
     * Zwraca identyfikator użytkownika powiązanego z danym tokenem.
     *
     * @param string $tokenValue
     * @return int|null
     */
    public function getUserIdByToken(string $tokenValue): ?int
    {
        $result = $this->createQueryBuilder('t')
            ->select('IDENTITY(t.user)')
            ->where('t.token = :tokenValue')
            ->setParameter('tokenValue', $tokenValue)
            ->getQuery()
            ->getSingleScalarResult();

        return $result !== null ? (int) $result : null;
    }

    /**
     * Zwraca registration_token z tabeli users na podstawie tokena z tabeli tokens.
     *
     * @param string $token
     * @return string|null
     */
    public function getUserTokenByToken(string $token): ?string
    {
        $result = $this->createQueryBuilder('t')
            ->select('u.registrationToken')
            ->join('t.user', 'u')
            ->where('t.token = :token')
            ->setParameter('token', $token)
            ->getQuery()
            ->getSingleScalarResult();

        if ($result) {
            $tokenToDelete = $this->createQueryBuilder('t')
                ->where('t.token = :token')
                ->setParameter('token', $token)
                ->getQuery()
                ->getSingleResult();
            $this->remove($tokenToDelete);

            return $result;
        }
        return null;
    }

    /**
     * Sprawdza, czy w tabeli tokens istnieje token o podanej wartości.
     *
     * @param string $tokenValue Wartość tokena do sprawdzenia.
     * @return bool Zwraca true, jeśli token istnieje, lub false, jeśli nie ma.
     */
    public function existsToken(string $tokenValue): bool
    {
        $token = $this->createQueryBuilder('t')
            ->select('t.token')
            ->where('t.token = :tokenValue')
            ->setParameter('tokenValue', $tokenValue)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $token !== null;
    }
}
