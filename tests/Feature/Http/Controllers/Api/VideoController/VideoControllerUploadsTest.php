<?php


namespace Tests\Feature\Http\Controllers\Api\VideoController;


use App\Models\Category;
use App\Models\Genre;
use App\Models\Video;
use Arr;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Http\UploadedFile;
use Tests\Traits\TestSaves;
use Tests\Traits\TestUploads;
use Tests\Traits\TestValidations;

class VideoControllerUploadsTest extends BaseVideoControllerTestCase
{
    use TestValidations, TestUploads, TestSaves;

    public function  testInvalidationVideoField(){

        $this->assertInvalidationFile(
            'video_file',
            'mp4',
            Video::VIDEO_FILE_MAX_SIZE,
            'mimetypes', ['values' => 'video/mp4']
        );
    }

    public function  testInvalidationThumbField(){

        $this->assertInvalidationFile(
            'thumb_file',
            'jpg',
            Video::THUMB_FILE_MAX_SIZE,
            'image'
        );
    }

    public function  testInvalidationBannerField(){

        $this->assertInvalidationFile(
            'banner_file',
            'jpg',
            Video::BANNER_FILE_MAX_SIZE,
            'image'
        );
    }

    public function  testInvalidationTraillerField(){

        $this->assertInvalidationFile(
            'trailer_file',
            'mp4',
            Video::TRAILER_FILE_MAX_SIZE,
            'mimetypes', ['values' => 'video/mp4']
        );
    }

    public function testSaveWithoutFiles(){

        $testData = Arr::except($this->sendData, ['categories_id', 'genres_id']);

        $data = [
            [
                'send_data' => $this->sendData ,
                'test_data' => $testData + ['opened' => false]
            ],
            [
                'send_data' => $this->sendData + [
                        'opened' => true,
                        ],
                'test_data' => $testData + ['opened' => true]
            ],
            [
                'send_data' => $this->sendData + [
                        'rating' => Video::RATING_LIST[1],
                    ],
                'test_data' => $testData + ['rating' => Video::RATING_LIST[1]]
            ]
        ];

        foreach ($data as $key => $value){

            $response = $this->assertStore($value['send_data'], $value['test_data'] + ['deleted_at' => null]);

            $response->assertJsonStructure([
                'created_at',
                'updated_at'
            ]);

            $this->assertHasCategory(
                $response->json('id'),
                $value['send_data']['categories_id'][0]
            );

            $this->assertHasGenre(
                $response->json('id'),
                $value['send_data']['genres_id'][0]
            );

            $response = $this->assertUpdate($value['send_data'], $value['test_data'] + ['deleted_at' => null]);

            $response->assertJsonStructure([
                'created_at',
                'updated_at'
            ]);

            $this->assertHasCategory(
                $response->json('id'),
                $value['send_data']['categories_id'][0]
            );

            $this->assertHasGenre(
                $response->json('id'),
                $value['send_data']['genres_id'][0]
            );

        }


    }

    public function assertHasCategory($videoId, $categoryId){
        $this->assertDatabaseHas('category_video',[
            'video_id' => $videoId,
            'category_id' => $categoryId
        ]);

    }

    public function assertHasGenre($videoId, $genreId){
        $this->assertDatabaseHas('genre_video',[
            'video_id' => $videoId,
            'genre_id' => $genreId
        ]);

    }

    public function testStoreWithFiles(){

        \Storage::fake();
        $files = $this->getFiles();

        $response = $this->json('POST', $this->routeStore(), $this->sendData + $files);

        $response->assertStatus(201);
        $this->assertFilesOnPersist($response, $files);

    }

    public function testUpdateWithFiles(){

        \Storage::fake();
        $files = $this->getFiles();

        $response = $this->json('PUT', $this->routeUpdate(), $this->sendData  + $files);

        $response->assertStatus(200);
        $this->assertFilesOnPersist($response, $files);

        $newFiles = [
            'thumb_file' => UploadedFile::fake()->create("thumb_file.jpg"),
            'video_file' => UploadedFile::fake()->create("video_file.mp4"),

        ];

        $response = $this->json('PUT', $this->routeUpdate(), $this->sendData  + $newFiles);

        $response->assertStatus(200);
        $this->assertFilesOnPersist($response, Arr::except($files, ['thumb_file', 'video_file']) + $newFiles);

        $id = $response->json('id');
        $video = Video::find($id);
        \Storage::assertMissing($video->relativeFilePath($files['thumb_file']->hashName()));
        \Storage::assertMissing($video->relativeFilePath($files['video_file']->hashName()));
    }

    public function assertFilesOnPersist(TestResponse $response, $files){
        $id= $response->json('id');
        $video = Video::find($id);
        $this->assertFilesExistsInStorage($video, $files);
    }

    protected function getFiles(){
        return [
            'thumb_file' => UploadedFile::fake()->create("thumb_file.jpg"),
            'banner_file' => UploadedFile::fake()->create("banner_file.jpg"),
            'trailer_file' => UploadedFile::fake()->create("trailer_file.mp4"),
            'video_file' => UploadedFile::fake()->create("video_file.mp4"),
        ];
    }


    protected function routeStore()
    {
        return route('videos.store');
    }

    protected function routeUpdate()
    {
        return route('videos.update', ['video' => $this->video->id]);
    }

    protected function model()
    {
        return Video::class;
    }
}
