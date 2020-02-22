<?php

namespace Tests\Unit\Models;

use App\Models\Category;
use App\Models\Genre;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\UploadedFile;
use Tests\Stubs\Models\UploadFilesStub;
use Tests\TestCase;
use App\Models\Traits\Uuid;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UploadFilesUnitTest extends TestCase
{
    private $obj;

    protected  function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        $this->obj = new UploadFilesStub();
    }

    public function testUploadFile(){

        \Storage::fake();

        $file = UploadedFile::fake()->create('video.mp4');

        $this->obj->uploadFile($file);

        \Storage::assertExists("1/{$file->hashName()}");


    }


    public function testUploadFiles(){

        \Storage::fake();

        $file1 = UploadedFile::fake()->create('video.mp4');
        $file2 = UploadedFile::fake()->create('video.mp4');

        $this->obj->uploadFiles([$file1, $file2]);

        \Storage::assertExists("1/{$file1->hashName()}");
        \Storage::assertExists("1/{$file2->hashName()}");


    }

    public function testDeleteFile(){
        \Storage::fake();
        $file = UploadedFile::fake()->create('video.mp4');

        $this->obj->uploadFile($file);
        $filename = $file->hashName();

        $this->obj->deleteFile($filename);

        \Storage::assertMissing("1/{$filename}");


        $file = UploadedFile::fake()->create('video.mp4');
        $this->obj->uploadFile($file);
        $this->obj->deleteFile($file);
        \Storage::assertMissing("1/{$file->hashName()}");
    }

    public function testDeleteFiles(){

        \Storage::fake();

        $file1 = UploadedFile::fake()->create('video.mp4');
        $file2 = UploadedFile::fake()->create('video.mp4');

        $this->obj->uploadFiles([$file1, $file2]);

        $this->obj->deleteFiles([$file1->hashName(), $file2]);

        \Storage::assertMissing("1/{$file1->hashName()}");
        \Storage::assertMissing("1/{$file2->hashName()}");

    }

    public function testextractFiles(){
        $attributes = [];
        $files = UploadFilesStub::extractFiles($attributes);
        $this->assertCount(0, $attributes);
        $this->assertCount(0, $files);

        $attributes = ['file1' => 'test'];
        $files = UploadFilesStub::extractFiles($attributes);
        $this->assertCount(1, $attributes);
        $this->assertEquals(['file1' => 'test'], $attributes);
        $this->assertCount(0, $files);

        $attributes = ['file1' => 'test', 'file2' => 'test'];
        $files = UploadFilesStub::extractFiles($attributes);
        $this->assertCount(2, $attributes);
        $this->assertEquals(['file1' => 'test', 'file2' => 'test'], $attributes);
        $this->assertCount(0, $files);

        $file1 = UploadedFile::fake()->create('video1.mp4');
        $attributes = ['file1' => $file1, 'other' => 'test'];
        $files = UploadFilesStub::extractFiles($attributes);
        $this->assertCount(2, $attributes);
        $this->assertEquals(['file1' => $file1->hashName(), 'other' => 'test'], $attributes);
        $this->assertCount(1, $files);
        $this->assertEquals([$file1], $files);


        $file2 = UploadedFile::fake()->create('video1.mp4');
        $attributes = ['file1' => $file1, 'file2'=> $file2,'other' => 'test'];
        $files = UploadFilesStub::extractFiles($attributes);
        $this->assertCount(3, $attributes);
        $this->assertEquals([
            'file1' => $file1->hashName(),
            'file2' => $file2->hashName(),
            'other' => 'test'
        ], $attributes);
        $this->assertCount(2, $files);
        $this->assertEquals([$file1, $file2], $files);

    }
}
