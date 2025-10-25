<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Project Policies & Help</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; }
        h1 { font-weight: 700; color: #2c3e50; }
        .card { border-radius: 1rem; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .nav-pills .nav-link { border-radius: 0.5rem; margin-right: 0.25rem; }
        .tab-content { min-height: 300px; }
        .card-header { background: #3498db; color: #fff; font-weight: 600; }
        @media (max-width: 767px) {
            .nav-pills { flex-wrap: wrap; }
        }
    </style>
</head>
<body>

<div class="container my-5">
    <h1 class="mb-4 text-center">Project Policies & Help</h1>

    <!-- Language Tabs -->
    <ul class="nav nav-tabs mb-4 justify-content-center" id="langTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="bn-lang-tab" data-bs-toggle="tab" data-bs-target="#bn-lang" type="button" role="tab" aria-controls="bn-lang" aria-selected="true">বাংলা</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="en-lang-tab" data-bs-toggle="tab" data-bs-target="#en-lang" type="button" role="tab" aria-controls="en-lang" aria-selected="false">English</button>
        </li>
    </ul>

    <div class="tab-content">
        <!-- বাংলা ল্যাঙ্গুয়েজ ট্যাব -->
        <div class="tab-pane fade show active" id="bn-lang" role="tabpanel" aria-labelledby="bn-lang-tab">
            <div class="card p-3">
                <!-- Page Navigation -->
                <ul class="nav nav-pills mb-3 flex-wrap justify-content-center" id="bnPageTab" role="tablist">
                    <li class="nav-item" role="presentation"><button class="nav-link active" data-bs-toggle="pill" data-bs-target="#terms_bn">টার্মস</button></li>
                    <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#privacy_bn">প্রাইভেসি</button></li>
                    <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#user_bn">ইউজার পলিসি</button></li>
                    <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#data_bn">ডেটা পলিসি</button></li>
                    <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#help_bn">FAQ</button></li>
                    <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#contact_bn">যোগাযোগ</button></li>
                    <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#payment_bn">পেমেন্ট</button></li>
                    <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#security_bn">সিকিউরিটি</button></li>
                </ul>

                <div class="tab-content p-3 border rounded">
                    <!-- Terms -->
                    <div class="tab-pane fade show active" id="terms_bn">
                        <h4 class="mb-3">টার্মস ও কন্ডিশনস</h4>
                        <p>সিস্টেম ব্যবহার করার পূর্বে ব্যবহারকারীকে এই শর্তাবলী মেনে চলতে হবে।</p>
                        <ul>
                            <li>ব্যবহারকারীর কন্টেন্ট ও তথ্যের দায়িত্ব ব্যবহারকারীর।</li>
                            <li>অবৈধ কার্যক্রম (হ্যাকিং, স্প্যাম, একাউন্ট মিসইউজ) নিষিদ্ধ।</li>
                            <li>অ্যাডমিন একাউন্ট স্থগিত বা বাতিল করার অধিকার রাখে।</li>
                        </ul>
                    </div>

                    <!-- Privacy -->
                    <div class="tab-pane fade" id="privacy_bn">
                        <h4 class="mb-3">প্রাইভেসি পলিসি</h4>
                        <ul>
                            <li>ব্যক্তিগত তথ্য তৃতীয় পক্ষের সাথে শেয়ার করা হবে না।</li>
                            <li>ডেটা এনক্রিপ্টেড এবং নিরাপদ সার্ভারে সংরক্ষিত।</li>
                            <li>ব্যাকআপ ও রিটেনশন নিয়মিত রাখা হবে।</li>
                        </ul>
                    </div>

                    <!-- User Policy -->
                    <div class="tab-pane fade" id="user_bn">
                        <h4 class="mb-3">ইউজার পলিসি</h4>
                        <ul>
                            <li>সিস্টেমে বিভিন্ন লেভেলের ব্যবহারকারী থাকতে পারে।</li>
                            <li>ফাইল আপলোড ও মেসেজিং দায়িত্বশীলভাবে করতে হবে।</li>
                            <li>নিয়ম ভঙ্গ করলে একাউন্ট সাময়িক/স্থায়ীভাবে বন্ধ হতে পারে।</li>
                        </ul>
                    </div>

                    <!-- Data Policy -->
                    <div class="tab-pane fade" id="data_bn">
                        <h4 class="mb-3">ডেটা পলিসি</h4>
                        <ul>
                            <li>ডেটা সংগ্রহ: ফর্ম, লগইন, API, লগ।</li>
                            <li>প্রতিটি লেভেল অনুযায়ী অ্যাক্সেস সীমিত।</li>
                            <li>ব্যাকআপ ও রিটেনশন নিয়মিত করা হয়।</li>
                            <li>ইউজার চাইলে ডেটা এক্সপোর্ট/ডিলিট করতে পারবে।</li>
                        </ul>
                    </div>

                    <!-- FAQ -->
                    <div class="tab-pane fade" id="help_bn">
                        <h4 class="mb-3">Help / FAQ</h4>
                        <p><strong>প্রশ্ন:</strong> কিভাবে নতুন অ্যাকাউন্ট তৈরি করব?<br>
                           <strong>উত্তর:</strong> হোমপেজে "সাইন আপ" ক্লিক করুন।</p>
                        <p><strong>প্রশ্ন:</strong> পাসওয়ার্ড ভুলে গেলে?<br>
                           <strong>উত্তর:</strong> "Forgot Password?" ক্লিক করুন।</p>
                        <p><strong>প্রশ্ন:</strong> ডেটা এক্সপোর্ট করা যাবে কি?<br>
                           <strong>উত্তর:</strong> প্রোফাইল থেকে "ডেটা এক্সপোর্ট" ব্যবহার করুন।</p>
                    </div>

                    <!-- Contact -->
                    <div class="tab-pane fade" id="contact_bn">
                        <h4 class="mb-3">যোগাযোগ / সাপোর্ট</h4>
                        <form action="contact_process.php" method="POST">
                            <div class="mb-3">
                                <label for="name_bn" class="form-label">নাম</label>
                                <input type="text" class="form-control" id="name_bn" name="name" required>
                            </div>
                            <div class="mb-3">
                                <label for="email_bn" class="form-label">ইমেইল</label>
                                <input type="email" class="form-control" id="email_bn" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="message_bn" class="form-label">মেসেজ</label>
                                <textarea class="form-control" id="message_bn" name="message" rows="4" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">পাঠান</button>
                        </form>
                        <p class="mt-3">ইমেইল: support@example.com <br> ফোন: +880123456789</p>
                    </div>

                    <!-- Payment -->
                    <div class="tab-pane fade" id="payment_bn">
                        <h4 class="mb-3">পেমেন্ট / রিফান্ড</h4>
                        <ul>
                            <li>নিরাপদ পেমেন্ট গেটওয়ে ব্যবহার করা হয়।</li>
                            <li>লেনদেন সাধারণত ফেরতযোগ্য নয়।</li>
                            <li>প্রযুক্তিগত সমস্যায় রিফান্ড দেওয়া হতে পারে।</li>
                        </ul>
                    </div>

                    <!-- Security -->
                    <div class="tab-pane fade" id="security_bn">
                        <h4 class="mb-3">সিকিউরিটি / রিপোর্ট</h4>
                        <ul>
                            <li>ভালনারেবিলিটি রিপোর্ট support@example.com এ পাঠাতে হবে।</li>
                            <li>প্রমাণ এবং বিস্তারিতসহ রিপোর্ট করতে হবে।</li>
                            <li>অবৈধ প্রবেশ চেষ্টা আইনি ব্যবস্থা এনে দিতে পারে।</li>
                            <li>গুরুত্বপূর্ণ রিপোর্টের জন্য Recognition বা ধন্যবাদ দেওয়া হতে পারে।</li>
                        </ul>
                    </div>

                </div>
            </div>
        </div>

       <!-- English Tab Content -->
<div class="tab-pane fade" id="en-lang" role="tabpanel" aria-labelledby="en-lang-tab">
    <div class="card p-3">
        <!-- Page Navigation -->
        <ul class="nav nav-pills mb-3 flex-wrap justify-content-center" id="enPageTab" role="tablist">
            <li class="nav-item" role="presentation"><button class="nav-link active" data-bs-toggle="pill" data-bs-target="#terms_en">Terms</button></li>
            <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#privacy_en">Privacy</button></li>
            <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#user_en">User Policy</button></li>
            <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#data_en">Data Policy</button></li>
            <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#help_en">FAQ</button></li>
            <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#contact_en">Contact</button></li>
            <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#payment_en">Payment</button></li>
            <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#security_en">Security</button></li>
        </ul>

        <div class="tab-content p-3 border rounded">
            <!-- Terms -->
            <div class="tab-pane fade show active" id="terms_en">
                <h4 class="mb-3">Terms & Conditions</h4>
                <p>Before using the system, users must comply with these terms and conditions. All users are legally responsible for their actions.</p>
                <ul>
                    <li>User content and data responsibility rests with the user.</li>
                    <li>Illegal activities such as hacking, spam, or misuse of accounts are prohibited.</li>
                    <li>Admins reserve the right to suspend or terminate accounts.</li>
                </ul>
            </div>

            <!-- Privacy -->
            <div class="tab-pane fade" id="privacy_en">
                <h4 class="mb-3">Privacy Policy</h4>
                <ul>
                    <li>Personal information will not be shared with third parties without permission.</li>
                    <li>All data is encrypted and stored securely on protected servers.</li>
                    <li>Regular data backup and retention procedures are followed.</li>
                </ul>
            </div>

            <!-- User Policy -->
            <div class="tab-pane fade" id="user_en">
                <h4 class="mb-3">User Policy</h4>
                <ul>
                    <li>The system has different user levels: Admin, Teacher, Student.</li>
                    <li>Users should upload files and message responsibly.</li>
                    <li>Violations of rules may result in temporary or permanent account suspension.</li>
                </ul>
            </div>

            <!-- Data Policy -->
            <div class="tab-pane fade" id="data_en">
                <h4 class="mb-3">Data Policy</h4>
                <ul>
                    <li>Data collection sources: forms, logins, APIs, system logs.</li>
                    <li>Access to data is limited based on user role.</li>
                    <li>Regular backup and retention are maintained.</li>
                    <li>Users may request data export or deletion.</li>
                </ul>
            </div>

            <!-- FAQ -->
            <div class="tab-pane fade" id="help_en">
                <h4 class="mb-3">Help / FAQ</h4>
                <p><strong>Q:</strong> How do I create a new account?<br>
                   <strong>A:</strong> Click "Sign Up" on the homepage and fill out the form.</p>
                <p><strong>Q:</strong> What if I forget my password?<br>
                   <strong>A:</strong> Click "Forgot Password?" on the login page.</p>
                <p><strong>Q:</strong> Can I export my data?<br>
                   <strong>A:</strong> Use the "Data Export" option in your profile.</p>
            </div>

            <!-- Contact -->
            <div class="tab-pane fade" id="contact_en">
                <h4 class="mb-3">Contact / Support</h4>
                <form action="contact_process.php" method="POST">
                    <div class="mb-3">
                        <label for="name_en" class="form-label">Name</label>
                        <input type="text" class="form-control" id="name_en" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="email_en" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email_en" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="message_en" class="form-label">Message</label>
                        <textarea class="form-control" id="message_en" name="message" rows="4" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Send</button>
                </form>
                <p class="mt-3">Email: support@example.com <br> Phone: +880123456789</p>
            </div>

            <!-- Payment -->
            <div class="tab-pane fade" id="payment_en">
                <h4 class="mb-3">Payment / Refund</h4>
                <ul>
                    <li>Secure payment gateways are used.</li>
                    <li>Transactions are generally non-refundable.</li>
                    <li>Refunds may be provided in case of technical issues.</li>
                </ul>
            </div>

            <!-- Security -->
            <div class="tab-pane fade" id="security_en">
                <h4 class="mb-3">Security / Vulnerability Disclosure</h4>
                <ul>
                    <li>Report vulnerabilities to support@example.com.</li>
                    <li>Include details and proof in your report.</li>
                    <li>Unauthorized access attempts may result in legal action.</li>
                    <li>Significant reports may receive acknowledgment or rewards.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
