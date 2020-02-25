<?php


namespace Tests\Feature\Http\Controllers\Api\VideoController;


use App\Models\Category;
use App\Models\Genre;
use App\Models\Video;
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
            '2048',
            'mimetypes', ['values' => 'video/mp4']
        );
    }

    public function testSaveWithoutFiles(){

        $category = factory(Category::class)->create();
        $genre = factory(Genre::class)->create();

        $genre->categories()->sync($category->id);


        $data = [
            [
                'send_data' => $this->sendData + [
                        'categories_id' => [$category->id],
                        'genres_id' => [$genre->id]
                    ],
                'test_data' => $this->sendData + ['opened' => false]
            ],
            [
                'send_data' => $this->sendData + [
                        'opened' => true,
                        'categories_id' => [$category->id],
                        'genres_id' => [$genre->id]
                    ],
                'test_data' => $this->sendData + ['opened' => true]
            ],
            [
                'send_data' => $this->sendData + [
                        'rating' => Video::RATING_LIST[1],
                        'categories_id' => [$category->id],
                        'genres_id' => [$genre->id]
                    ],
                'test_data' => $this->sendData + ['rating' => Video::RATING_LIST[1]]
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

        $category = factory(Category::class)->create();
        $genre = factory(Genre::class)->create();
        $genre->categories()->sync($category);

        $response = $this->json('POST', $this->routeStore(), $this->sendData + [
                'categories_id' => [$category->id],
                'genres_id' => [$genre->id],

            ] + $files

        );

        $response->assertStatus(201);
        $id = $response->json('id');
        foreach ($files as $file){
            \Storage::assertExists("$id/{$file->hashName()}");
        }
    }

    public function testUpdateWithFiles(){

        \Storage::fake();
        $files = $this->getFiles();

        $category = factory(Category::class)->create();
        $genre = factory(Genre::class)->create();
        $genre->categories()->sync($category);

        $response = $this->json('PUT', $this->routeUpdate(), $this->sendData + [
                'categories_id' => [$category->id],
                'genres_id' => [$genre->id],

            ] + $files

        );

        $response->assertStatus(200);
        $id = $response->json('id');
        foreach ($files as $file){
            \Storage::assertExists("$id/{$file->hashName()}");
        }
    }

    protected function getFiles(){
        return [
            'video_file' => UploadedFile::fake()->create("video_file.mp4")
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
