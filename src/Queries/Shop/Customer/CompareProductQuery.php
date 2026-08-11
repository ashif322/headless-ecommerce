<?php

namespace Webkul\GraphQLAPI\Queries\Shop\Customer;

use Illuminate\Database\Eloquent\Builder;
use Webkul\Customer\Repositories\CompareItemRepository;
use Webkul\GraphQLAPI\Queries\BaseFilter;
use Webkul\Product\Repositories\ProductRepository;

class CompareProductQuery extends BaseFilter
{
    /**
     * Create a new instance.
     */
    public function __construct(
        protected ProductRepository $productRepository,
        protected CompareItemRepository $compareItemRepository
    ) {}

    /**
     * Filter query for compare products.
     */
    public function __invoke(Builder $query, array $input): Builder
    {
        $customer = bagisto_graphql()->authorize();

        $query->distinct()
            ->select('compare_items.*')
            ->leftJoin('product_flat', 'compare_items.product_id', '=', 'product_flat.product_id')
            ->where('compare_items.customer_id', $customer->id);

        $filters = [
            'compare_items.id'         => $input['id'] ?? null,
            'compare_items.product_id' => $input['product_id'] ?? null,
            'product_flat.price'       => $input['price'] ?? null,
        ];

        $query = $this->applyFilter($query, $filters);

        $query->when(! empty($input['product_name']), function ($query) use ($input) {
            $query->where('product_flat.name', 'like', '%'.$input['product_name'].'%');
        });

        return $query->groupBy('compare_items.id');
    }

    /**
     * Get the specified compare product.
     */
    public function getItem(Builder $query): Builder
    {
        $customer = bagisto_graphql()->authorize();

        return $query->where('customer_id', $customer->id);
    }

    /**
     * Get products for the compare page — one query for guests and customers.
     *
     */
    public function getProductsByIds(mixed $rootValue, array $args)
    {
        try {
            $customer = auth()->guard('api')->user();
        } catch (\Exception $e) {
            $customer = null;
        }

        if ($customer) {
            $productIds = $this->compareItemRepository
                ->findByField('customer_id', $customer->id)
                ->pluck('product_id')
                ->toArray();
        } else {
            $productIds = $args['productIds'] ?? [];
        }

        if (empty($productIds)) {
            return [];
        }

        return $this->productRepository->whereIn('id', $productIds)->get();
    }
}
