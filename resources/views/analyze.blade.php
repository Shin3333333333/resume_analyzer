<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
        <div class="col-12 col-xl-11">

            <!-- Header -->
            <header class="header-section mt-4 mb-4 text-center">
                <h1 class="display-5">AI Resume Analyzer & Job Matcher</h1>
                <p class="lead text-muted">
                    Upload your resume and compare it with a job description using AI-powered analysis.
                </p>
            </header>

            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <!-- SIDE-BY-SIDE GRID -->
            <div class="row g-4">
                <!-- LEFT COLUMN: Resume Preview -->
              <div class="col-12 col-lg-6">
            <!-- Resume Preview Card -->
            <div class="card h-100">
                <div class="card-header bg-dark text-white">
                    Uploaded Resume Preview
                </div>
                <div class="card-body position-relative" id="resumeContentWrapper" style="max-height: 500px; overflow-y: auto; min-height: 200px;">

                    <!-- Loader (hidden by default) -->
                    <div id="resumeLoader"
                        style="display: none; position: absolute; top: 0; left: 0;
                            width: 100%; height: 100%; background: rgba(255,255,255,0.8);
                            align-items: center; justify-content: center; z-index: 10;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>

                    <!-- Resume Content / Placeholder -->
                    <div id="resumeContent" style="height: 100%; width: 100%;">
                        @if(session('resume'))
                            {!! session('resume')->resume_content !!}
                        @else
                            <div class="d-flex align-items-center justify-content-center text-muted"
                                style="height: 100%; width: 100%;">
                                <p class="mb-0 text-center">
                                    Your resume preview will appear here after analysis.
                                </p>
                            </div>
                        @endif
                    </div>


                </div>
            </div>
</div>




                <!-- RIGHT COLUMN: Upload + Job Description -->
                <div class="col-12 col-lg-6">
                    <div class="card ats-style-card h-100">
                        <div class="card-body p-4 p-md-5">

                            <form id="analysisForm" enctype="multipart/form-data">
                                @csrf

                                <!-- Resume Upload -->
                                <div class="mb-4">
                                    <label for="resumeFile" class="form-label fs-5">
                                        Upload Your Resume
                                    </label>
                                    <input class="form-control"
                                           type="file"
                                           id="resumeFile"
                                           name="resume_file"
                                           accept=".pdf,.doc,.docx"
                                           required>
                                    <div class="form-text">
                                        Accepted formats: PDF, DOC, DOCX.
                                    </div>
                                </div>

                                <!-- Job Description -->
                                <div class="mb-4">
                                    <label for="jobDescription" class="form-label fs-5">
                                        Paste Job Description
                                    </label>
                                    <textarea class="form-control"
                                              id="jobDescription"
                                              name="job_description"
                                              rows="10"
                                              required
                                              placeholder="Paste the full job description here..."></textarea>
                                </div>

                                <!-- Submit -->
                                <div class="d-grid">
                                    <button type="submit"
                                            id="submitButton"
                                            class="btn btn-primary btn-lg"
                                            disabled>
                                        <span id="buttonText">Analyze Resume</span>
                                        <span id="loadingSpinner"
                                              class="spinner-border spinner-border-sm"
                                              style="display:none;"></span>
                                    </button>
                                </div>
                            </form>

                        </div>
                    </div>
                </div>

            </div>
            <!-- END GRID -->
    <div id="resumeSuggestions" class="card mt-3 p-3 text-muted" style="display:none;"></div>

        </div>
    </div>
</div>


    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

<!-- Load app.js first -->
@vite(['resources/js/app.js'])

<!-- Loading modal partial -->
@include('components.loading-modal')

<!-- Inline JS for form submit -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('analysisForm');
    const resumeInput = document.getElementById('resumeFile');
    const jobDescriptionInput = document.getElementById('jobDescription');
    const submitButton = document.getElementById('submitButton');
    const buttonText = document.getElementById('buttonText');
    const loadingSpinner = document.getElementById('loadingSpinner');
    const resumeContentDiv = document.getElementById('resumeContent');

    let resumeFileValid = false;
    const allowedExtensions = ['pdf', 'doc', 'docx', 'txt'];

    function validateForm() {
        submitButton.disabled =
            !resumeFileValid || jobDescriptionInput.value.trim() === '';
    }

    resumeInput.addEventListener('change', function () {
        const file = resumeInput.files[0];
        if (!file) {
            resumeFileValid = false;
            validateForm();
            return;
        }

        const ext = file.name.split('.').pop().toLowerCase();
        if (!allowedExtensions.includes(ext)) {
            alert('Invalid file type.');
            resumeInput.value = '';
            resumeFileValid = false;
        } else {
            resumeFileValid = true;
        }
        validateForm();
    });

    jobDescriptionInput.addEventListener('input', validateForm);

    const loader = document.getElementById('resumeLoader');
form.addEventListener('submit', function(e) {
    e.preventDefault();

    // Check if a file is selected
    const file = resumeInput.files[0];
    if (!file) {
        alert('Please upload a resume first.');
        return; // stop submission
    }

    submitButton.disabled = true;
    buttonText.textContent = 'Analyzing...';
    loadingSpinner.style.display = 'inline-block';

    // ✅ Show loader inside Resume Preview only if file exists
    loader.style.display = 'flex';

    const formData = new FormData(form);
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

    fetch('{{ route("analyze.resume.ajax") }}', { method: 'POST', body: formData })
        .then(async res => {
            const text = await res.text();
            let data;
            try {
                data = JSON.parse(text);
            } catch (e) {
                console.error('Non-JSON response:', text);
                throw new Error('Invalid JSON response');
            }

            if (!res.ok || !data.success) {
                throw data;
            }

            return data;
        })
        .then(data => {
                // Update resume content
                resumeContentDiv.innerHTML = data.resume.resume_content;

                // Show AI suggestions if they exist
                let suggestionDiv = document.getElementById('resumeSuggestions');
                if(!suggestionDiv) {
                    // create dynamically if it doesn't exist
                    suggestionDiv = document.createElement('div');
                    suggestionDiv.id = 'resumeSuggestions';
                    suggestionDiv.style.marginTop = '1rem';
                    suggestionDiv.style.padding = '0.5rem';
                    suggestionDiv.style.borderTop = '1px solid #dee2e6';
                    document.getElementById('resumeContentWrapper').appendChild(suggestionDiv);
                }

                if(data.resume.suggestions) {
                    suggestionDiv.style.display = 'block';
                    suggestionDiv.innerHTML = "<strong>Burat:</strong><br>" + data.resume.suggestions;
                } else {
                    suggestionDiv.style.display = 'none';
                }
            })


        .catch(err => {
            console.error('Handled error:', err);
            if (err?.errors) {
                alert(Object.values(err.errors)[0][0]);
            } else if (err?.message) {
                alert(err.message);
            }
        })
        .finally(() => {
            loader.style.display = 'none';
            submitButton.disabled = false;
            buttonText.textContent = 'Analyze Resume';
            loadingSpinner.style.display = 'none';
        });
});


});
</script>
</body>
</html>