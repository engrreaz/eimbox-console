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

    <h1>প্রাইভেসি পলিসি / Privacy Policy</h1>

    <div class="tabs float-right">
        <div class="tab active" data-tab="bn">বাংলা</div>
        <div class="tab" data-tab="en">English</div>
    </div>

    <div id="bn" class="content active">
        <h2>স্বাগতম!</h2>
        <p>এই নথিটি [PROJECT_NAME] দ্বারা পরিচালিত সফটওয়্যার সার্ভিস/ওয়েবসাইট/অ্যাপ্লিকেশনের জন্য প্রযোজ্য। আমাদের কাছে
            আপনার গোপনীয়তা অত্যন্ত গুরুত্বপূর্ণ।</p>

        <h3>১. আমরা কোন তথ্য সংগ্রহ করি</h3>
        <ul>
            <li>ব্যক্তিগত তথ্য: নাম, ইমেইল, ফোন, ঠিকানা।</li>
            <li>প্রোফাইল তথ্য: অ্যাকাউন্ট তথ্য, পছন্দসই সেটিংস।</li>
            <li>প্রযুক্তিগত তথ্য: IP ঠিকানা, ব্রাউজার টাইপ, লগ তথ্য।</li>
            <li>কুকিজ ও অনুরূপ প্রযুক্তি।</li>
            <li>পেমেন্ট তথ্য (যদি প্রযোজ্য)।</li>
            <li>গ্রাহক সহায়তা তথ্য।</li>
        </ul>

        <h3>২. তথ্য ব্যবহারের উদ্দেশ্য</h3>
        <ul>
            <li>সার্ভিস প্রদান ও পরিচালনা।</li>
            <li>ব্যবহারকারীর অভিজ্ঞতা উন্নত করা।</li>
            <li>গ্রাহক সহায়তা।</li>
            <li>বিলিং ও লেনদেন।</li>
            <li>আইনি বাধ্যবাধকতা পূরণ।</li>
            <li>মার্কেটিং ও যোগাযোগ (সম্মতি থাকলে)।</li>
        </ul>

        <h3>৩. ডাটা নিরাপত্তা</h3>
        <p>আমরা এনক্রিপশন ও অ্যাক্সেস কন্ট্রোল ব্যবহার করি, কিন্তু কোনো সিস্টেম ১০০% নিরাপদ নয়।</p>

        <h3>৪. ব্যবহারকারীর অধিকার</h3>
        <ul>
            <li>তথ্য অ্যাক্সেস করা।</li>
            <li>ভুল তথ্য সংশোধন করা।</li>
            <li>ডাটা মুছে ফেলার অনুরোধ করা।</li>
            <li>প্রক্রিয়াকরণ সীমাবদ্ধ করা।</li>
            <li>ডাটা পোর্টেবিলিটি।</li>
            <li>বিপণন সম্মতি প্রত্যাহার।</li>
        </ul>

        <h3>৫. শিশুদের তথ্য</h3>
        <p>১৩ বছরের কম শিশুদের তথ্য আমরা জানিয়ে না সংগ্রহ করি।</p>

        <h3>যোগাযোগ</h3>
        <p>ইমেইল: [CONTACT_EMAIL]<br>ঠিকানা: [BUSINESS_ADDRESS]</p>
    </div>

    <div id="en" class="content">
        <h2>Welcome!</h2>
        <p>This document applies to [PROJECT_NAME] software service/website/app. Your privacy is important to us.</p>

        <h3>1. Information We Collect</h3>
        <ul>
            <li>Personal Data: Name, email, phone, address.</li>
            <li>Profile Data: Account information, preferences.</li>
            <li>Technical Data: IP address, browser type, logs.</li>
            <li>Cookies and similar technologies.</li>
            <li>Payment Data (if applicable).</li>
            <li>Customer support data.</li>
        </ul>

        <h3>2. How We Use Your Information</h3>
        <ul>
            <li>Provide and maintain services.</li>
            <li>Improve user experience.</li>
            <li>Customer support.</li>
            <li>Billing and transactions.</li>
            <li>Legal compliance.</li>
            <li>Marketing communications (with consent).</li>
        </ul>

        <h3>3. Data Security</h3>
        <p>We use encryption and access control, but no system is 100% secure.</p>

        <h3>4. Your Rights</h3>
        <ul>
            <li>Access your data.</li>
            <li>Correct inaccurate data.</li>
            <li>Request data deletion.</li>
            <li>Restrict processing.</li>
            <li>Data portability.</li>
            <li>Withdraw marketing consent.</li>
        </ul>

        <h3>5. Children’s Information</h3>
        <p>We do not knowingly collect data from children under 13 years.</p>

        <h3>Contact</h3>
        <p>Email: [CONTACT_EMAIL]<br>Address: [BUSINESS_ADDRESS]</p>
    </div>







    <?php require_once 'footer.php'; ?>

    <!-- ----------------------------------- -->
    <script></script>

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

    <!-- ----------------------------------- -->
</body>

</html>