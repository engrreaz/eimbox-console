<?php require_once 'header.php'; ?>

<style>
    .tabs {
        display: flex;
        border-bottom: 1px solid #ccc;
        cursor: pointer;
    }

    .tab {
        padding: 10px 20px;
        margin-right: 5px;
        background: #f1f1f1;
    }

    .tab.active {
        background: #fff;
        border-bottom: none;
    }

    .content {
        display: none;
        padding: 20px;
        border-top: none;
    }

    .content.active {
        display: block;
    }

    h2 {
        margin-top: 0;
    }

    ul {
        padding-left: 20px;
    }
</style>
</head>

<body>

<h1>টার্মস এন্ড কন্ডিশন / Terms & Conditions</h1>

<div class="tabs float-right">
    <div class="tab active" data-tab="bn">বাংলা</div>
    <div class="tab" data-tab="en">English</div>
</div>

<div id="bn" class="content active">
    <h2>১. ভূমিকা</h2>
    <p>এই শর্তাবলী ([PROJECT_NAME]) ওয়েবসাইট/সার্ভিস/অ্যাপ্লিকেশনের ব্যবহার সম্পর্কিত। সাইট ব্যবহার করে আপনি এই শর্তাবলীতে সম্মত হচ্ছেন।</p>

    <h3>২. অ্যাকাউন্ট ও নিরাপত্তা</h3>
    <ul>
        <li>আপনার অ্যাকাউন্ট তথ্য নিরাপদ রাখা।</li>
        <li>অননুমোদিত কার্যকলাপের জন্য আপনি দায়ী থাকবেন।</li>
    </ul>

    <h3>৩. ব্যবহার এবং নিষিদ্ধ কার্যকলাপ</h3>
    <ul>
        <li>আইন-বিদ্বেষী, ক্ষতিকর বা স্প্যামিং কার্যকলাপ করবেন না।</li>
        <li>সিস্টেম ভাঙা বা অন্য ব্যবহারকারীর তথ্য চুরি করা নিষিদ্ধ।</li>
    </ul>

    <h3>৪. কপিরাইট ও বৌদ্ধিক সম্পত্তি</h3>
    <p>ওয়েবসাইটের সমস্ত কন্টেন্ট কোম্পানি বা লাইসেন্সদাতার মালিকানাধীন। অনুমতি ছাড়া ব্যবহার করা যাবে না।</p>

    <h3>৫. দায় ও ওয়ারেন্টি</h3>
    <p>সার্ভিস "যেমন আছে" ভিত্তিতে প্রদান করা হয়; কোম্পানি কোনও ক্ষতির জন্য দায়ী থাকবে না।</p>

    <h3>যোগাযোগ</h3>
    <p>ইমেইল: [CONTACT_EMAIL]<br>ঠিকানা: [BUSINESS_ADDRESS]</p>
</div>

<div id="en" class="content">
    <h2>1. Introduction</h2>
    <p>These Terms and Conditions apply to [PROJECT_NAME] website/service/app. By using the site, you agree to these terms.</p>

    <h3>2. Account & Security</h3>
    <ul>
        <li>Keep your account information secure.</li>
        <li>You are responsible for any unauthorized activity.</li>
    </ul>

    <h3>3. Usage & Prohibited Activities</h3>
    <ul>
        <li>No unlawful, harmful, or spam activity.</li>
        <li>No system hacking or stealing other users’ information.</li>
    </ul>

    <h3>4. Copyright & Intellectual Property</h3>
    <p>All website content is owned by the company or its licensors. Unauthorized use is prohibited.</p>

    <h3>5. Liability & Warranty</h3>
    <p>Service is provided "as is"; the company is not liable for any damages.</p>

    <h3>Contact</h3>
    <p>Email: [CONTACT_EMAIL]<br>Address: [BUSINESS_ADDRESS]</p>
</div>

<?php require_once 'footer.php'; ?>

<script>
    const tabs = document.querySelectorAll('.tab');
    const contents = document.querySelectorAll('.content');

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            tabs.forEach(t => t.classList.remove('active'));
            contents.forEach(c => c.classList.remove('active'));
            tab.classList.add('active');
            document.getElementById(tab.dataset.tab).classList.add('active');
        });
    });
</script>

</body>
</html>
