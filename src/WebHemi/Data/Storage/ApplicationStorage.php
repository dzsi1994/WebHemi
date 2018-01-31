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
     * @return null|array
     */
    public function getApplicationList(
        int $limit = QueryInterface::MAX_ROW_LIMIT,
        int $offset = 0
    ) : ? array {
        $applications = null;
        $this->normalizeLimitAndOffset($limit, $offset);

        $data = $this->queryAdapter->fetchData(
            'getApplicationList',
            [
                ':limit' => $limit,
                ':offset' => $offset
            ]
        );

        foreach ($data as $row) {
            $applications[$row['name']] = $row;
        }

        return $applications;
    }

    /**
     * Returns a Application entity identified by (unique) ID.
     *
     * @param  int $identifier
     * @return null|array
     */
    public function getApplicationById($identifier) : ? array
    {
        $data = $this->queryAdapter->fetchData('getApplicationById', [':idApplication' => $identifier]);

        return $data[0] ?? null;
    }

    /**
     * Returns an Application entity by name.
     *
     * @param  string $name
     * @return null|array
     */
    public function getApplicationByName(string $name) : ? array
    {
        $data = $this->queryAdapter->fetchData('getApplicationById', [':name' => $name]);

        return $data[0] ?? null;
    }
}
