<?php declare(strict_types=1);

namespace Selpol\Entity\Repository\Dvr;

use Selpol\Entity\Criteria;
use Selpol\Entity\Model\Dvr\DvrServer;
use Selpol\Entity\Repository;
use Selpol\Service\Database\Page;

/**
 * @method DvrServer fetchRaw(string $query, array $params = [])
 * @method DvrServer[] fetchAllRaw(string $query, array $params = [])
 * @method Page<DvrServer> fetchPaginate(int $page, int $size, ?Criteria $criteria = null)
 *
 * @method DvrServer findById(mixed $id)
 *
 * @extends Repository<int, DvrServer>
 */
class DvrServerRepository extends Repository
{
    protected bool $audit = true;

    protected function __construct()
    {
        parent::__construct(DvrServer::class);
    }
}