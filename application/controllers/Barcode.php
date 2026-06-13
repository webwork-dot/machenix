<?php
use Picqer\Barcode\BarcodeGeneratorPNG;

class Barcode extends CI_Controller
{
    public function index()
    {
      
        $generator = new Picqer\Barcode\BarcodeGeneratorPNG();

        file_put_contents(
            FCPATH . 'uploads/product_123.png',
            $generator->getBarcode(
                '123456789',
                $generator::TYPE_CODE_128
            )
        );
    }

    public function inventory_batch()
    {
        $generator = new Picqer\Barcode\BarcodeGeneratorPNG();

        file_put_contents(
            FCPATH . 'uploads/product_123.png',
            $generator->getBarcode(
                '123456789',
                $generator::TYPE_CODE_128
            )
        );
    }
}