<?php

namespace App\Domain\Config;

enum OverwritePolicy: string
{
    case Skip = 'skip';
    case Replace = 'replace';
    case Merge = 'merge';
    case Cancel = 'cancel';
}
