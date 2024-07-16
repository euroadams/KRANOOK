<?php
declare(strict_types = 1);

namespace Endroid\QrCode\BaconQrCode\Renderer;

use Endroid\QrCode\BaconQrCode\Encoder\QrCode;

interface RendererInterface
{
    public function render(QrCode $qrCode) : string;
}
