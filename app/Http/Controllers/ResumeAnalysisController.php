<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use League\CommonMark\CommonMarkConverter;
use PhpOffice\PhpWord\IOFactory;
use Smalot\PdfParser\Parser as PdfParser;

class ResumeAnalysisController extends Controller
{
    // 🔹 OPTIONAL: old non-AJAX version (can delete if unused)
    public function store(Request $request)
    {
        // if you are using AJAX, this method is NOT used
        return redirect()->back();
    }

    // 🔹 AJAX VERSION (THIS IS THE IMPORTANT ONE)
 public function storeAjax(Request $request)
{
    // 1️⃣ Validate AJAX request
    $validator = \Validator::make($request->all(), [
        'resume_file' => 'required|mimetypes:application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,text/plain|max:5120',
        'job_description' => 'required|string',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'errors' => $validator->errors()
        ], 422);
    }

    $file = $request->file('resume_file');
    $extension = strtolower($file->getClientOriginalExtension());
    $resumeContent = '';

    // 2️⃣ Move file to temp path
    $tempPath = sys_get_temp_dir() . '/' . uniqid() . '.' . $extension;
    $file->move(dirname($tempPath), basename($tempPath));

    try {
        switch ($extension) {
            case 'txt':
                $resumeContent = '<pre>' . htmlspecialchars(file_get_contents($tempPath)) . '</pre>';
                break;

            case 'doc':
            case 'docx':
                $phpWord = IOFactory::load($tempPath);
                $writer = IOFactory::createWriter($phpWord, 'HTML');
                ob_start();
                $writer->save('php://output');
                $resumeContent = ob_get_clean();
                break;

            case 'pdf':
                $parser = new PdfParser();
                $pdf = $parser->parseFile($tempPath);
                $resumeContent = '<pre>' . htmlspecialchars($pdf->getText()) . '</pre>';
                break;

            default:
                throw new \Exception('Unsupported file type.');
        }
    } catch (\Exception $e) {
        @unlink($tempPath);
        return response()->json([
            'success' => false,
            'message' => 'Failed to read resume: ' . $e->getMessage()
        ], 500);
    }

    @unlink($tempPath);

    // 3️⃣ Insert into DB (your existing logic)
    $insertResult = DB::select('CALL usp_insert_resume(?, ?, ?)', [
        1,
        $resumeContent,
        $request->job_description
    ]);

    $newId = $insertResult[0]->inserted_id;

    $result = DB::select('CALL usp_get_resume(?, ?)', [1, $newId]);
    if (!$result) {
        return response()->json([
            'success' => false,
            'message' => 'No resume found'
        ], 500);
    }

    $resumeText = html_entity_decode($resumeContent, ENT_QUOTES | ENT_HTML5);

    // Remove CSS blocks
    $resumeText = preg_replace('/@page\s+[^{]+\{[^}]*\}/i', '', $resumeText);
    $resumeText = preg_replace('/\.[a-zA-Z0-9_-]+\s*\{[^}]*\}/', '', $resumeText);

    // Remove remaining HTML
    $resumeText = strip_tags($resumeText);

    // Remove Word junk words
    $resumeText = preg_replace('/(Hyperlink|WordSection\d+|NormalTable)/i', '', $resumeText);

    // Normalize whitespace
    $resumeText = preg_replace('/\r\n|\r/', "\n", $resumeText);
    $resumeText = preg_replace('/\n{2,}/', "\n", $resumeText);
    $resumeText = preg_replace('/\s{2,}/', ' ', $resumeText);

    $resumeText = trim($resumeText);


    $jobDescription = $request->job_description;

    // 4️⃣ Hugging Face AI Suggestions
    // 4️⃣ Hugging Face AI Suggestions
    $suggestions = 'No suggestions available.';
    try {
        $client = new \GuzzleHttp\Client();
        $apiKey = env('HUGGINGFACE_API_KEY');

        $response = $client->post('https://router.huggingface.co/v1/chat/completions', [
            'headers' => [
                'Authorization' => "Bearer $apiKey",
                'Content-Type' => 'application/json',
            ],
            'json' => [
                "model" => "Qwen/Qwen3-Coder-Next:novita",
                "messages" => [
                    [
                        "role" => "user",
                        "content" => "Suggest improvements to make the following resume more professional and aligned with this job description.\n\nResume:\n$resumeText\n\nJob Description:\n$jobDescription"
                    ]
                ],
                "stream" => false
            ]
        ]);

        $body = json_decode($response->getBody()->getContents(), true);
        if (isset($body['choices'][0]['message']['content'])) {
            $markdown = $body['choices'][0]['message']['content'];

            $converter = new CommonMarkConverter([
                'html_input' => 'strip',          // security
                'allow_unsafe_links' => false,
            ]);

            $suggestions = $converter->convert($markdown)->getContent();
        }

            } catch (\Exception $e) {
                \Log::error('HF AI Error: ' . $e->getMessage());
            }


    // 5️⃣ Return JSON with resume + AI suggestions
    return response()->json([
        'success' => true,
        'resume' => [
            'resume_content' => $resumeContent,
            'suggestions' => $suggestions
        ]
    ]);
}

}

