<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpWord\IOFactory;
use Smalot\PdfParser\Parser as PdfParser;

class ResumeAnalysisController extends Controller
{
    public function store(Request $request)
    {
        // 1️⃣ Validate input (file types only)
        $request->validate([
            'resume_file' => 'required|mimes:pdf,doc,docx,txt|max:5120', // 5MB limit
            'job_description' => 'required|string',
        ]);

        $file = $request->file('resume_file');
        $extension = strtolower($file->getClientOriginalExtension());
        $resumeContent = '';

        // 2️⃣ Extract content based on file type
        switch ($extension) {
            case 'txt':
                // Wrap plain text in <pre> to preserve formatting
                $resumeContent = '<pre>' . htmlspecialchars(file_get_contents($file->getPathname())) . '</pre>';
                break;

            case 'doc':
            case 'docx':
                // Convert DOCX to HTML
                $phpWord = IOFactory::load($file->getPathname());
                $writer = IOFactory::createWriter($phpWord, 'HTML');
                ob_start();
                $writer->save("php://output");
                $resumeContent = ob_get_clean();
                break;

            case 'pdf':
                // Convert PDF to text
                $parser = new PdfParser();
                $pdf = $parser->parseFile($file->getPathname());
                $text = $pdf->getText();
                $resumeContent = '<pre>' . htmlspecialchars($text) . '</pre>';
                break;

            default:
                return back()->with('error', 'Unsupported file type.');
        }

        // 3️⃣ Call MySQL Stored Procedure with content instead of path
        $success = DB::statement('CALL insert_resume_analysis_to_db(1,?, ?)', [
            $resumeContent,
            $request->job_description
        ]);

        if ($success) {
            return back()->with('success', 'Resume analyzed and stored successfully!');
        }

        return back()->with('error', 'Failed to insert resume.');
    }
}
