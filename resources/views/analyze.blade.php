<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Resume Analyzer & Job Matcher</title>
    
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    
    <style>
        body {
            background-color: #f8f9fa;
        }
        .ats-style-card {
            border: 1px solid #dee2e6;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
        }
        .header-section {
            text-align: center;
            padding: 2.5rem 1.5rem;
            background-color: #ffffff;
            border-bottom: 1px solid #dee2e6;
        }
        .main-content {
            padding-top: 2rem;
            padding-bottom: 4rem;
        }
    </style>
</head>
<body>

    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-10 col-xl-8">
                
                <header class="header-section mt-4">
                    <h1 class="display-5">AI Resume Analyzer & Job Matcher</h1>
                    <p class="lead text-muted">
                        Upload your resume and compare it with a job description using AI-powered analysis.
                    </p>
                </header>

                <main class="main-content">
                    @if(session('success'))
                        <div class="alert alert-success mt-3">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="card ats-style-card">
                        <div class="card-body p-4 p-md-5">
                            <form id="analysisForm" 
                                action="{{ route('analyze.resume') }}" 
                                method="POST" 
                                enctype="multipart/form-data">
                                @csrf

                                <!-- Resume Upload -->
                                <div class="mb-4">
                                    <label for="resumeFile" class="form-label fs-5">Upload Your Resume</label>
                                    <input class="form-control" type="file" id="resumeFile" name="resume_file" accept=".pdf,.doc,.docx" required>
                                    <div id="resumeHelp" class="form-text">Accepted formats: PDF, DOC, DOCX.</div>
                                </div>

                                <!-- Job Description Textarea -->
                                <div class="mb-4">
                                    <label for="jobDescription" class="form-label fs-5">Paste Job Description</label>
                                    <textarea class="form-control" id="jobDescription" name="job_description" rows="10" placeholder="Paste the full job description here to analyze it against your resume." required></textarea>
                                </div>

                                <!-- Submit Button -->
                                <div class="d-grid">
                                    <button type="submit" id="submitButton" class="btn btn-primary btn-lg" disabled>
                                        <span id="buttonText">Analyze Resume</span>
                                        <span id="loadingSpinner" class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="display: none;"></span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </main>

            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
    const resumeInput = document.getElementById('resumeFile');
    const jobDescriptionInput = document.getElementById('jobDescription');
    const submitButton = document.getElementById('submitButton');
    const buttonText = document.getElementById('buttonText');
    const loadingSpinner = document.getElementById('loadingSpinner');

    let resumeFileValid = false;

    // Allowed file types
    const allowedExtensions = ['pdf', 'doc', 'docx', 'txt'];

    // Enable/disable submit button based on validation
    function validateForm() {
        submitButton.disabled = !resumeFileValid || jobDescriptionInput.value.trim() === '';
    }

    // File validation
    resumeInput.addEventListener('change', function () {
        const file = resumeInput.files[0];
        if (!file) {
            resumeFileValid = false;
            validateForm();
            return;
        }

        const ext = file.name.split('.').pop().toLowerCase();
        if (!allowedExtensions.includes(ext)) {
            alert('Invalid file type. Please upload PDF, DOC, DOCX, or TXT.');
            resumeInput.value = '';
            resumeFileValid = false;
        } else {
            resumeFileValid = true;
        }

        validateForm();
    });

    // Job description input listener
    jobDescriptionInput.addEventListener('input', validateForm);

    // Show loading spinner on submit
    document.getElementById('analysisForm').addEventListener('submit', function () {
        submitButton.disabled = true;
        buttonText.textContent = 'Analyzing...';
        loadingSpinner.style.display = 'inline-block';
    });
});
</script>


</body>
</html>