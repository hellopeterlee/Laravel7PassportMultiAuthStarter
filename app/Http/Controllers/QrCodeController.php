<?php
/**
 * Created by PhpStorm.
 * Date: 2021/12/31 15:36
 */

namespace App\Http\Controllers;

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Response\QrCodeResponse;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class QrCodeController extends Controller
{
    /**
     * @throws \Exception
     */
    public function index(Request $request)
    {
        $text = $request->get('text');
        $size = $request->get('size', 300);
        $margin = $request->get('margin', 10);
        $qrCode = new QrCode($text);
        $qrCode->setSize($size);
        $qrCode->setMargin($margin);

        $writer = new PngWriter();
        $result = $writer->write($qrCode);
        header('Content-Type: ' . $result->getMimeType());
        echo $result->getString();
        echo $result->getDataUri();
    }
}
