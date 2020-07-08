<?php

namespace Lof\AutoInvoice\Api;

/**
 * @api
 */
interface InvoiceProcessInterface
{
    /**
     * Returns a list of items to process.
     * Every item consists of an order, and a destination status.
     * 
     * @returns \Lof\AutoInvoice\Api\Data\InvoiceProcessItemInterface
     */
    public function getItemsToProcess();
    
    /**
     * Invoice order
     * 
     * @param \Lof\AutoInvoice\Api\Data\InvoiceProcessItemInterface $item
     */
    public function invoice(\Lof\AutoInvoice\Api\Data\InvoiceProcessItemInterface $item);
}
