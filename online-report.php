<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Online Report - Barangay San Miguel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'primary': '#0E2F65',
                        'secondary': '#1D4ED8',
                    }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex flex-col">
        <!-- Header -->
        <header class="bg-primary text-white p-6 shadow-lg">
            <div class="container mx-auto flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <img src="pics/brgylogo.png" alt="Logo" class="h-16">
                    <div>
                        <h1 class="text-2xl font-bold">Barangay San Miguel</h1>
                        <p class="text-sm">Online Incident Report Submission</p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <img src="pics/Pasig.png" alt="Pasig Logo" class="h-12 object-contain">
                    <img src="pics/Pasig circle.png" alt="Pasig Circle Logo" class="h-12 object-contain">
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 container mx-auto p-6 max-w-4xl">
            <div class="bg-white rounded-lg shadow-md p-8 mb-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">Submit an Incident Report Online</h2>
                <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6">
                    <p class="text-sm text-gray-700">
                        <strong>Note:</strong> This form allows you to submit an incident report online.
                        Your report will be reviewed by our desk officers and may be converted to an official case.
                        Please provide accurate and complete information.
                    </p>
                </div>

                <form id="onlineReportForm" class="space-y-6">
                    <!-- Submitter Information -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-700 mb-3 border-b pb-2">Your Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Full Name <span class="text-red-500">*</span></label>
                                <input type="text" name="submitter_name" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email Address <span class="text-red-500">*</span></label>
                                <input type="email" name="submitter_email" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number <span class="text-red-500">*</span></label>
                                <input type="tel" name="submitter_phone" required pattern="[0-9]{11}" placeholder="09XXXXXXXXX"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                        </div>
                    </div>

                    <!-- Incident Information -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-700 mb-3 border-b pb-2">Incident Details</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Incident Date & Time <span class="text-red-500">*</span></label>
                                <input type="datetime-local" name="incident_datetime" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Type of Complaint <span class="text-red-500">*</span></label>
                                <select name="complaint_type" required id="complaintType"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="">Select Type</option>
                                    <option value="Noise Complaints">Noise Complaints</option>
                                    <option value="Neighbor Disputes">Neighbor Disputes</option>
                                    <option value="Mischief/Vandalism">Mischief/Vandalism</option>
                                    <option value="Pet-Related Incidents">Pet-Related Incidents</option>
                                    <option value="Minor Theft">Minor Theft</option>
                                    <option value="Others">Others (Please specify)</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-4" id="otherComplaintDiv" style="display: none;">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Specify Other Complaint Type <span class="text-red-500">*</span></label>
                            <input type="text" name="other_complaint_type" id="otherComplaintInput"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Incident Location <span class="text-red-500">*</span></label>
                            <input type="text" name="incident_location" required placeholder="Street, Barangay, City"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Incident Description <span class="text-red-500">*</span></label>
                            <textarea name="incident_description" required rows="6"
                                      placeholder="Please provide a detailed description of the incident..."
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                        </div>
                    </div>

                    <!-- Terms and Submit -->
                    <div class="border-t pt-4">
                        <div class="flex items-start mb-4">
                            <input type="checkbox" id="termsCheckbox" required class="mt-1 mr-2">
                            <label for="termsCheckbox" class="text-sm text-gray-600">
                                I certify that the information provided is true and correct to the best of my knowledge.
                                I understand that providing false information may result in legal consequences.
                            </label>
                        </div>
                        <div class="flex justify-between items-center">
                            <a href="index.php" class="text-blue-600 hover:underline">Back to Login</a>
                            <button type="submit" id="submitBtn"
                                    class="bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700 transition">
                                Submit Report
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Success Message (Hidden by default) -->
            <div id="successMessage" class="bg-green-50 border-l-4 border-green-500 p-4 rounded-lg hidden">
                <div class="flex items-center">
                    <svg class="h-6 w-6 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <div>
                        <h3 class="text-lg font-semibold text-green-800">Report Submitted Successfully!</h3>
                        <p class="text-sm text-green-700">Your report has been received and will be reviewed by our desk officers. You will be contacted via email or phone for updates.</p>
                    </div>
                </div>
            </div>

            <!-- Error Message (Hidden by default) -->
            <div id="errorMessage" class="bg-red-50 border-l-4 border-red-500 p-4 rounded-lg hidden">
                <div class="flex items-center">
                    <svg class="h-6 w-6 text-red-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    <div>
                        <h3 class="text-lg font-semibold text-red-800">Submission Failed</h3>
                        <p class="text-sm text-red-700" id="errorText">An error occurred. Please try again.</p>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-gray-800 text-white p-6 mt-auto">
            <div class="container mx-auto text-center">
                <p class="text-sm">&copy; 2025 Barangay San Miguel, Pasig City. All rights reserved.</p>
                <p class="text-xs mt-2">For emergencies, please call 911 or contact your local police station.</p>
            </div>
        </footer>
    </div>

    <script>
        // Handle "Others" complaint type
        document.getElementById('complaintType').addEventListener('change', function() {
            const otherDiv = document.getElementById('otherComplaintDiv');
            const otherInput = document.getElementById('otherComplaintInput');

            if (this.value === 'Others') {
                otherDiv.style.display = 'block';
                otherInput.required = true;
            } else {
                otherDiv.style.display = 'none';
                otherInput.required = false;
                otherInput.value = '';
            }
        });

        // Handle form submission
        document.getElementById('onlineReportForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const submitBtn = document.getElementById('submitBtn');
            const successMsg = document.getElementById('successMessage');
            const errorMsg = document.getElementById('errorMessage');

            // Hide previous messages
            successMsg.classList.add('hidden');
            errorMsg.classList.add('hidden');

            // Disable submit button
            submitBtn.disabled = true;
            submitBtn.textContent = 'Submitting...';

            try {
                const formData = new FormData(this);

                const response = await fetch('actions/submit_online_report.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    successMsg.classList.remove('hidden');
                    this.reset();
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                } else {
                    throw new Error(result.message || 'Submission failed');
                }
            } catch (error) {
                errorMsg.classList.remove('hidden');
                document.getElementById('errorText').textContent = error.message;
                window.scrollTo({ top: 0, behavior: 'smooth' });
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Submit Report';
            }
        });
    </script>
</body>
</html>
