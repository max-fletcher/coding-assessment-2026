<?php

/**
 * Invoice Class
 *
 * Handles invoice creation and management
 * Started: 2 weeks ago
 * Last modified: Friday (was in a hurry)
 */
class Invoice {

    private $customer;
    private $items = [];
    private $discount = 0;
    private $id;
    private $createdAt;

    public function __construct($customerName) {
        $this->customer = $customerName;
        $this->id = uniqid(); // Not sure if this is the best approach...
        $this->createdAt = date('Y-m-d H:i:s');
    }

    /**
     * Add an item to the invoice
     * Note: Make sure to use consistent naming!
     */
    public function addItem(string $name, float $price, int $quantity): void{
        $this->validateName($name);
        $this->validatePrice($price);
        $this->validateQuantity($quantity);

        // No validation yet - add later?
        $this->items[] = [
            'name' => $name,
            'price' => $price,
            'qty' => $quantity  // Using 'qty' here
        ];
    }

    /**
     * Calculate total
     * BUG: This doesn't match up with addItem() - need to fix
     */
    public function getTotal() {
        $total = 0;
        foreach ($this->items as $item) {
            // Accessing 'quantity' but we stored it as 'qty'!
            $total += $item['price'] * $item['qty'];
        }
        return $total - $this->discount;
    }

    /**
     * Apply discount to invoice
     * TODO: Should discounts apply before or after tax?
     * TODO: Client hasn't decided on the business rules yet
     */
    public function applyDiscount($percent) {
        // Started implementing but not sure about requirements
        // throw new Exception("Not implemented - waiting on client clarification");

        // Trying basic implementation but commented out until we get clarity
        // $subtotal = $this->getTotal();
        // $this->discount = $subtotal * ($percent / 100);

        // For now just throw exception
        throw new Exception("Discount feature incomplete - need business rules from client");
    }

    /**
     * Get invoice ID
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Get customer name
     */
    public function getCustomer() {
        return $this->customer;
    }

    /**
     * Get items array
     */
    public function getItems() {
        return $this->items;
    }

    /**
     * Convert invoice to array for JSON serialization
     */
    public function toArray() {
        return [
            'id' => $this->id,
            'customer' => $this->customer,
            'items' => $this->items,
            'discount' => $this->discount,
            'total' => $this->getTotal(),
            'created_at' => $this->createdAt
        ];
    }

    /**
     * Save invoice to file
     * FIXME: This overwrites everything! Need to fix but running out of time
     * Should APPEND to the file, not replace it
     */
    public function saveToFile($filename = '/../data/invoices.json')
    {
        $data = $this->toArray();

        $filename = dirname(__DIR__) . '/data/test_invoices.json';

        // file_put_contents('debug.log', "Filename: {$filename}\n", FILE_APPEND);
        // file_put_contents(
        //     'debug.log',
        //     "File exists: " . (file_exists($filename) ? 'YES' : 'NO') . "\n",
        //     FILE_APPEND
        // );

        if (file_exists($filename)) {
            $existingFileContents = file_get_contents($filename);
            $existingInvoiceData = json_decode($existingFileContents, true) ?? [];
        } else {
            $existingInvoiceData = [];
        }

        // file_put_contents(
        //     'debug.log',
        //     "Existing data:\n" . print_r($existingInvoiceData, true) . "\n",
        //     FILE_APPEND
        // );

        $appendedDataToExistingIvoiceData = [...$existingInvoiceData, $data];

        // file_put_contents(
        //     'debug.log',
        //     "append Existing data:\n" . print_r($appendedDataToExistingIvoiceData, true) . "\n",
        //     FILE_APPEND
        // );

        // TEMP: overwrites file
        file_put_contents(
            $filename,
            json_encode($appendedDataToExistingIvoiceData, JSON_PRETTY_PRINT),
            LOCK_EX
        );

        return true;
    }

    /**
     * Load invoice from file by ID
     * Started this but didn't finish testing it
     */
    public static function loadFromFile($id, $filename = 'data/invoices.json') {
        if (!file_exists($filename)) {
            throw new Exception("Invoice file not found");
        }

        $contents = file_get_contents($filename);
        $invoices = json_decode($contents, true);

        // file_put_contents(
        //     'debug.log',
        //     print_r($contents, true),
        //     FILE_APPEND
        // );

        
        // file_put_contents(
        //     'debug.log',
        //     print_r($invoices, true),
        //     FILE_APPEND
        // );


        // Handle both single invoice and array of invoices
        // (since saveToFile is broken and only saves one)
        if (isset($invoices['id'])) {
            $invoices = [$invoices];
        }

        // echo($invoices);

        foreach ($invoices as $invoiceData) {
            if ($invoiceData['id'] == $id) {
                $invoice = new Invoice($invoiceData['customer']);
                $invoice->id = $invoiceData['id'];
                $invoice->discount = $invoiceData['discount'];

                foreach ($invoiceData['items'] as $item) {
                    // echo($item);
                    // This might break because of the qty/quantity issue
                    $qty = $item['qty'];
                    $invoice->addItem($item['name'], $item['price'], $qty);
                }

                return $invoice;
            }
        }

        throw new Exception("Invoice not found: " . $id);
    }

    private function validateName(string $name): void{
        if(gettype($name) != 'string')
            throw new Exception('Name has to be a string');

        return;
    }

    private function validatePrice(float $price): void {
        if($price < 0)
            throw new Exception('Price cannot be less than zero');

        return;
    }

    private function validateQuantity(int $quantity): void {
        if($quantity < 0)
            throw new Exception('Quantity cannot be less than zero');

        return;
    }
}
