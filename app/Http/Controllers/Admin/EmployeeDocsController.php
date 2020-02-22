<?php

namespace App\Http\Controllers\Admin;

use App\EmployeeDocs;
use App\Helper\Reply;
use App\Http\Requests\EmployeeDocs\CreateRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;

class EmployeeDocsController extends AdminBaseController
{

    private $mimeType = [
        'txt' => 'fa-file-text',
        'htm' => 'fa-file-code-o',
        'html' => 'fa-file-code-o',
        'php' => 'fa-file-code-o',
        'css' => 'fa-file-code-o',
        'js' => 'fa-file-code-o',
        'json' => 'fa-file-code-o',
        'xml' => 'fa-file-code-o',
        'swf' => 'fa-file-o',
        'flv' => 'fa-file-video-o',

        // images
        'png' => 'fa-file-image-o',
        'jpe' => 'fa-file-image-o',
        'jpeg' => 'fa-file-image-o',
        'jpg' => 'fa-file-image-o',
        'gif' => 'fa-file-image-o',
        'bmp' => 'fa-file-image-o',
        'ico' => 'fa-file-image-o',
        'tiff' => 'fa-file-image-o',
        'tif' => 'fa-file-image-o',
        'svg' => 'fa-file-image-o',
        'svgz' => 'fa-file-image-o',

        // archives
        'zip' => 'fa-file-o',
        'rar' => 'fa-file-o',
        'exe' => 'fa-file-o',
        'msi' => 'fa-file-o',
        'cab' => 'fa-file-o',

        // audio/video
        'mp3' => 'fa-file-audio-o',
        'qt' => 'fa-file-video-o',
        'mov' => 'fa-file-video-o',
        'mp4' => 'fa-file-video-o',
        'mkv' => 'fa-file-video-o',
        'avi' => 'fa-file-video-o',
        'wmv' => 'fa-file-video-o',
        'mpg' => 'fa-file-video-o',
        'mp2' => 'fa-file-video-o',
        'mpeg' => 'fa-file-video-o',
        'mpe' => 'fa-file-video-o',
        'mpv' => 'fa-file-video-o',
        '3gp' => 'fa-file-video-o',
        'm4v' => 'fa-file-video-o',

        // adobe
        'pdf' => 'fa-file-pdf-o',
        'psd' => 'fa-file-image-o',
        'ai' => 'fa-file-o',
        'eps' => 'fa-file-o',
        'ps' => 'fa-file-o',

        // ms office
        'doc' => 'fa-file-text',
        'rtf' => 'fa-file-text',
        'xls' => 'fa-file-excel-o',
        'ppt' => 'fa-file-powerpoint-o',
        'docx' => 'fa-file-text',
        'xlsx' => 'fa-file-excel-o',
        'pptx' => 'fa-file-powerpoint-o',


        // open office
        'odt' => 'fa-file-text',
        'ods' => 'fa-file-text',
    ];

    /**
     * ManageLeadFilesController constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->pageIcon = 'icon-layers';
        $this->pageTitle = 'app.menu.employeeDocs';
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }


    /**
     * @param Request $request
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Throwable
     */
    public function store(CreateRequest $request)
    {
        $fileFormats = ['image/jpeg','image/png','image/gif','application/msword','application/vnd.openxmlformats-officedocument.wordprocessingml.document','application/vnd.ms-excel','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet','application/pdf','text/plain'];
        foreach ($request->file as $index => $fFormat) {
            if (!in_array($fFormat->getClientMimeType(), $fileFormats)){
                return Reply::error('This file format not allowed');
            }
        }

        $storage = config('filesystems.default');
        foreach ($request->name as $index => $name) {
            if(isset($request->file[$index])){
                $value = $request->file[$index];
                if ($value != '' && $name != '' && $value != null && $name != null) {
                    $file = new EmployeeDocs();
                    $file->user_id = $request->user_id;
                    switch($storage) {
                        case 'local':
                            $value->storeAs('user-uploads/employee-docs/'.$request->user_id, $value->hashName());
                            break;
                        case 's3':
                            Storage::disk('s3')->putFileAs('employee-docs/'.$request->user_id, $value, $value->hashName(), 'public');
                            break;
                        case 'google':
                            $dir = '/';
                            $recursive = false;
                            $contents = collect(Storage::cloud()->listContents($dir, $recursive));
                            $dir = $contents->where('type', '=', 'dir')
                                ->where('filename', '=', 'employee-docs')
                                ->first();

                            if(!$dir) {
                                Storage::cloud()->makeDirectory('employee-docs');
                            }

                            $directory = $dir['path'];
                            $recursive = false;
                            $contents = collect(Storage::cloud()->listContents($directory, $recursive));
                            $directory = $contents->where('type', '=', 'dir')
                                ->where('filename', '=', $request->user_id)
                                ->first();

                            if ( ! $directory) {
                                Storage::cloud()->makeDirectory($dir['path'].'/'.$request->user_id);
                                $contents = collect(Storage::cloud()->listContents($directory, $recursive));
                                $directory = $contents->where('type', '=', 'dir')
                                    ->where('filename', '=', $request->user_id)
                                    ->first();
                            }

                            Storage::cloud()->putFileAs($directory['basename'], $value, $value->hashName());

                            $file->google_url = Storage::cloud()->url($directory['path'].'/'.$value->hashName());

                            break;
                        case 'dropbox':
                            Storage::disk('dropbox')->putFileAs('employee-docs/'.$request->user_id.'/', $value, $value->hashName());
                            $dropbox = new Client(['headers' => ['Authorization' => "Bearer ".config('filesystems.disks.dropbox.token'), "Content-Type" => "application/json"]]);
                            $res = $dropbox->request('POST', 'https://api.dropboxapi.com/2/sharing/create_shared_link_with_settings',
                                [\GuzzleHttp\RequestOptions::JSON => ["path" => '/employee-docs/'.$request->user_id.'/'.$value->hashName()]]
                            );
                            $dropboxResult = $res->getBody();
                            $dropboxResult = json_decode($dropboxResult, true);
                            $file->dropbox_link = $dropboxResult['url'];
                            break;
                    }

                    $file->name = $name;
                    $file->filename = $value->getClientOriginalName();
                    $file->hashname = $value->hashName();
                    $file->size = $value->getSize();
                    $file->save();
                }
            }

        }

        $this->employeeDocs = EmployeeDocs::where('user_id', $request->user_id)->get();

        $view = view('admin.employees.docs-list', $this->data)->render();

        return Reply::successWithData(__('messages.fileUploaded'), ['html' => $view]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
//        $this->lead = Lead::findOrFail($id);
//        return view('admin.lead.employee-docs.show', $this->data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }


    /**
     * @param Request $request
     * @param $id
     * @return array
     * @throws \Throwable
     */
    public function destroy(Request $request, $id)
    {
        $file = EmployeeDocs::findOrFail($id);
        $storage = config('filesystems.default');

        switch($storage) {
            case 'local':
                File::delete('user-uploads/employee-docs/'.$file->user_id.'/'.$file->filename);
                break;
            case 's3':
                Storage::disk('s3')->delete('employee-docs/'.$file->user_id.'/'.$file->filename);
                break;
            case 'google':
                Storage::disk('google')->delete('employee-docs/'.$file->user_id.'/'.$file->filename);
                break;
            case 'dropbox':
                Storage::disk('dropbox')->delete('employee-docs/'.$file->user_id.'/'.$file->filename);
                break;
        }

        EmployeeDocs::destroy($id);

        $this->employeeDocs = EmployeeDocs::where('user_id', $file->user_id)->get();

        $view = view('admin.employees.docs-list', $this->data)->render();

        return Reply::successWithData(__('messages.fileDeleted'), ['html' => $view]);
    }


    /**
     * @param $id
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function download($id) {
        $file = EmployeeDocs::findOrFail($id);
        $storage = config('filesystems.default');
        switch($storage) {
            case 'local':
                return response()->download('user-uploads/employee-docs/'.$file->user_id.'/'.$file->hashname, $file->filename);
                break;
            case 's3':
                $ext = pathinfo($file->filename, PATHINFO_EXTENSION);
                $fs = Storage::getDriver();
                $stream = $fs->readStream('employee-docs/'.$file->user_id.'/'.$file->filename);
                return Response::stream(function() use($stream) {
                    fpassthru($stream);
                }, 200, [
                    "Content-Type" => $ext,
                    "Content-Length" => $file->size,
                    "Content-disposition" => "attachment; filename=\"" .basename($file->filename) . "\"",
                ]);
                break;
            case 'google':
                $ext = pathinfo($file->filename, PATHINFO_EXTENSION);
                $dir = '/';
                $recursive = false; // Get subdirectories also?
                $contents = collect(Storage::cloud()->listContents($dir, $recursive));
                $directory = $contents->where('type', '=', 'dir')
                    ->where('filename', '=', 'employee-docs')
                    ->first();

                $direct = $directory['path'];
                $recursive = false;
                $contents = collect(Storage::cloud()->listContents($direct, $recursive));
                $directo = $contents->where('type', '=', 'dir')
                    ->where('filename', '=', $file->user_id)
                    ->first();

                $readStream = Storage::cloud()->getDriver()->readStream($directo['path']);
                return response()->stream(function () use ($readStream) {
                    fpassthru($readStream);
                }, 200, [
                    'Content-Type' => $ext,
                    'Content-disposition' => 'attachment; filename="'.$file->filename.'"',
                ]);
                break;
            case 'dropbox':
                $ext = pathinfo($file->filename, PATHINFO_EXTENSION);
                $fs = Storage::getDriver();
                $stream = $fs->readStream('employee-docs/'.$file->user_id.'/'.$file->filename);
                return Response::stream(function() use($stream) {
                    fpassthru($stream);
                }, 200, [
                    "Content-Type" => $ext,
                    "Content-Length" => $file->size,
                    "Content-disposition" => "attachment; filename=\"" .basename($file->filename) . "\"",
                ]);
                break;
        }

    }

}
