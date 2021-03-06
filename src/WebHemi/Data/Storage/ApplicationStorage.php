<?php
/**
 * WebHemi.
 *
 * PHP version 7.1
 *
 * @copyright 2012 - 2018 Gixx-web (http://www.gixx-web.com)
 * @license   https://opensource.org/licenses/MIT The MIT License (MIT)
 *
 * @link http://www.gixx-web.com
 */
declare(strict_types = 1);

namespace WebHemi\Data\Storage;

use WebHemi\Data\Entity\ApplicationEntity;
use WebHemi\Data\Entity\EntitySet;
use WebHemi\Data\Query\QueryInterface;

/**
 * Class ApplicationStorage.
 */
class ApplicationStorage extends AbstractStorage
{
    /**
     * Returns every Application entity.
     *
     * @param int $limit
     * @param int $offset
     * @return EntitySet
     */
    public function getApplicationList(
        int $limit = QueryInterface::MAX_ROW_LIMIT,
        int $offset = 0
    ) : EntitySet {
        $this->normalizeLimitAndOffset($limit, $offset);

        $data = $this->getQueryAdapter()->fetchData(
            'getApplicationList',
            [
                ':limit' => $limit,
                ':offset' => $offset
            ]
        );

        return $this->getEntitySet(ApplicationEntity::class, $data);
    }

    /**
     * Returns a Application entity identified by (unique) ID.
     *
     * @param  int $identifier
     * @return null|ApplicationEntity
     */
    public function getApplicationById(int $identifier) : ? ApplicationEntity
    {
        $data = $this->getQueryAdapter()->fetchData(
            'getApplicationById',
            [
                ':idApplication' => $identifier
            ]
        );

        /** @var null|ApplicationEntity $entity */
        $entity = $this->getEntity(ApplicationEntity::class, $data[0] ?? []);

        return $entity;
    }

    /**
     * Returns an Application entity by name.
     *
     * @param  string $name
     * @return null|ApplicationEntity
     */
    public function getApplicationByName(string $name) : ? ApplicationEntity
    {
        $data = $this->getQueryAdapter()->fetchData(
            'getApplicationByName',
            [
                ':name' => $name
            ]
        );

        /** @var null|ApplicationEntity $entity */
        $entity = $this->getEntity(ApplicationEntity::class, $data[0] ?? []);

        return $entity;
    }
}
