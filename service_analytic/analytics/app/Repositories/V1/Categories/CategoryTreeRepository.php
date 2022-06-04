<?php

namespace App\Repositories\V1\Categories;

use App\Contracts\Repositories\V1\Categories\CategoryTreeRepositoryInterface;
use App\Models\Categories\CategoryTree;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class CategoryTreeRepository implements CategoryTreeRepositoryInterface
{

    /**
     * Из вложенного множества собираем дерево категорий
     *
     * @return array
     */
    public function getTreeArray(): array
    {
        $categories = CategoryTree::query()
            ->where('tree', 1)
            ->orderBy('name')
            ->get()->toArray();

        $max_depth = max(array_column($categories, 'depth'));
        $tree_levels = array_fill(0, $max_depth, []);
        foreach ($categories as $node) {
            $tree_levels[$node['depth']][] = $node + ['children' => []];
        }

        $build = function (&$tree_categories) use (&$build, $tree_levels) {
            $l = $tree_categories['depth'] + 1;

            if (!isset($tree_levels[$l])) {
                return;
            }

            foreach ($tree_levels[$l] as &$n) {
                if (($n['lft'] >= $tree_categories['lft']) && ($n['rgt'] <= $tree_categories['rgt'])) {
                    $result = [
                        'web_id' => $n['web_id'],
                        'name' => $n['name'],
                        'url' => $n['url'],
                        'path' => $n['path'],
                        'children' => &$n['children']
                    ];
                    $tree_categories['children'][] = $result;
                    $build($n);
                }
            }
        };

        $tree_categories = $tree_levels[0][0];
        $build($tree_categories);

        return $tree_categories;
    }

    /**
     * @return array
     */
    public function getCachedCategoryTree(): array
    {
        return Cache::remember(
            md5(sprintf("%s_%s", __METHOD__, __CLASS__)),
            Carbon::now()->secondsUntilEndOfDay(),
            function () {
                return $this->getTreeArray();
            }
        );
    }
}
