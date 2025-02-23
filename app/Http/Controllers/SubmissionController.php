<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubmissionFormRequest;
use App\Models\Submission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SubmissionController extends Controller
{
    public function store(SubmissionFormRequest $request)
    {
        DB::beginTransaction();

        try {
            $data = $request->validated();
            unset($data['images'], $data['files']);

            $submission = Submission::create($data);

            if ($request->hasFile('images')) {
                $files = $request->file('images');
                $images = [];

                // If only one file is uploaded, convert it to an array
                if (!is_array($files)) {
                    $files = [$files];
                }

                foreach ($files as $file) {
                    $path = $file->store('submissions', 'public');
                    $images[] = $path;
                }

                $submission->update(['images' => json_encode($images)]);
            }

            if ($request->hasFile('files')) {
                $files = $request->file('files');
                $pdfs = [];

                if (!is_array($files)) {
                    $files = [$files];
                }

                foreach ($files as $file) {
                    $path = $file->store('submissions', 'public');
                    $pdfs[] = $path;
                }

                $submission->update(['files' => json_encode($pdfs)]);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Contact details saved successfully.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'failed',
                'message' => 'Something went wrong! Please try again later. #error:' . $e->getMessage()
            ]);
        }
    }
}
