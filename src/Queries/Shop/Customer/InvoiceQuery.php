<?php

namespace Webkul\GraphQLAPI\Queries\Shop\Customer;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\URL;
use Webkul\Sales\Repositories\InvoiceRepository;

class InvoiceQuery
{
    /**
     * Create a new instance.
     */
    public function __construct(
        protected InvoiceRepository $invoiceRepository
    ) {}

    /**
     * Filter query for order invoices.
     */
    public function __invoke(mixed $query, array $input): Builder
    {
        $customer = bagisto_graphql()->authorize();

        $params = Arr::except($input, ['invoice_date']);

        $query->when(Arr::has($input, 'invoice_date'), function ($query) use ($input) {
            $query->whereDate('created_at', $input['invoice_date']);
        });

        $query->whereHas('order', function ($query) use ($customer) {
            $query->where('customer_id', $customer->id);
        });

        return $query->where($params)->orderBy('id', 'desc');
    }

    /**
     * Get the specified order invoice.
     */
    public function getInvoice(Builder $query): Builder
    {
        $customer = bagisto_graphql()->authorize();

        return $query->whereHas('order', function ($query) use ($customer) {
            $query->where('customer_id', $customer->id);
        });
    }

    /**
     * Generate URL for invoice PDF download.
     */
    public function getInvoiceUrl($invoice): ?string
    {
        try {
            $invoiceModel = $this->invoiceRepository->find($invoice->id);

            if (! $invoiceModel) {
                return null;
            }

            return URL::signedRoute(
                'shop.customers.account.orders.print-invoice',
                ['id' => $invoice->id]
            );

        } catch (\Exception $e) {
            return null;
        }
    }
}