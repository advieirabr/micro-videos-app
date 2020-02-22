<?php

namespace Tests\Stubs\Models;

use App\Models\Traits\UploadFiles;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Schema\Blueprint;


class UploadFilesStub extends Model
{
    use UploadFiles;

    protected function uploadDIr()
    {
        return "1";
    }
}
