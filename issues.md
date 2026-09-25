# EIMBox Web Portal Application Issues for fix & Refactor

##  Important Instructions :
    - Always user sweetalert for any alert message
    - table schema is located in sql/ folder.
    - All php file should be utf-8 encoded
    - Essencial cdn, js files are included in header.php, footer.php
    - Don't change any logic that I edit/write manually.
    - admin means admin >0 (usersapp table), who is actually developer, super administraotr, technical team member, support desk team.
    - core/functions.php files holds some functions like, login, teacher photo, student photo, result/grade calculation etc.
    - Subject Name are depend on sccategory, becasue same subject code has differnt subject name for differnt sccategory.
   - 'components/slot-tree-ui.php' is a UI component for slot tree structure, I have used it in many places, So, never modify or remove this line if it have in any script. It is a cascade chain selection tool.
    - in all CRUD (create, update) operations, allways update modifieddate coloum with current timestamp. It's very important.
    - always try to keep existing table structure. Tables are connected with other platforms, app etc.





## Issues : 
1.  academic-calendar.php (Tools -> Academic Calendar)
    [ ] re-factor script with class, work settings in modal/popup . 

2.  academics-classes.php (Academic -> Classes & Sections)

3.  accounts-manager.php (Finance -> Charts of Account)

4.  admission-list.php (Authority -> Admission)

5.  admit-card.php (Examination -> Admit Card)

6.  analytics-exam-report.php (Reports -> Overall Exam Report)

7.  analytics-exam.php (Reports -> Exam Analytics)

8.  attendance-register.php (Attendance -> Attendance Register)
    [x] attendance grid এ বর্তমানে weekends গুলো মার্ক করা আছে। এতে ইভেন্টসগুলোও মার্ক করতে চাই।  events.sql টেবিল থেকে ডেটা নিতে হবে। 

9.  bank-account.php (Finance -> Bank Account)
    ------ Next ----------------------

10. bank-manager.php (Finance -> Bank Manager)
    [x] add/edit bank account popup ---- bank name dropdown list বানাও, ব্যাংক লিষ্ট banklist.sql থেকে আসবে। 
    [x] প্রতিষ্ঠান নতুন ব্যাংক লিষ্টে নতুন নাম যুক্ত করার সুযোগ দাও। 
    [x] অ্যাকাউন্ট ক্লোজ করার জন্য closingdate সেট করার ব্যবস্থা করো। 
    [x] টেবিলে ক্লোজিং অ্যাকাউন্টগুলো একসাথে সবার নিচে দেখাও বা ফিল্টার করার সুযোগ দাও।

11. cash-book.php (Finance -> Cash Book)
    --------------------------------------

12. cashbook-report.php (Finance -> Cashbook Report)
    --------------------------------

13. class-routine.php (Academics -> Class Routine)
    [x] Re-design, refactor this module that user setup their class routine easily. You may build this in grid system Day - Period (Colurm-row) system. users will set subject & teacher with one click. can fillout nextday routine in one click.
    [x] weekends ডেটা নিতে হবে settings টেবিল থেকে  setting_title ='weekends' দিয়ে কোয়েরী করে settings_value এর মান ডট সেপারেট করে বের করতে হবে। 
    [x] রুটিন সেট  করার সময় মডাল/পপআপে যে বিষয়ের নাম দেখায়, সেখানে sccategory দিয়ে বিষয়ের নাম ফেচ করা হয়ণি।

14. class-schedule.php (Academics -> Class Schedule)
    [x] drop down action button 

15. customize-settings-progress-report.php (Settings -> Progress Report)

16. daily-collection-summery.php (Payment -> Daily Collection Report)
    ------------------------------------

17. daily-reports.php (Reports  -> Daily Reports)

18. data-duplicator.php (Tools -> Data Duplicator)

19. enroll-students.php (Student -> Enroll Student)
    ----------------------------------

20. exam-manager.php (Examination -> Exam List)
    [x] ‍actions buttons make 3 dot drop down.


21. exam-routine.php (Examination -> Exam Routine)
    [x] fix clone routine/import routine


22. guest-student-panel-settings.php (Settings -> Guest Panel)

23. index.php (Core -> Dashboard)

24. institute-profile.php (Authority -> Institute Profile)

25. list-all.php (Student -> Student List All)

26. managing-voter-list.php (Tools -> Manage Voter List)
    [x] পিতা-মাতার NID এবং মোবাইল নম্বরের ভিত্তিতে সিবলিং ক্লাস্টার সনাক্তকরণ অ্যালগরিদম বাস্তবায়ন (Class Six to Twelve, Session %2digit% matching)।
    [x] Check NID এবং Check Mobile ডায়াগনস্টিক অডিট মডাল ও ডেটা হেলথ অ্যানালিটিক্স কার্ড।
    [x] সিবলিং প্রিভিউ মডাল ও ষষ্ঠ শ্রেণি থেকে ক্রমানুযায়ী ১, ২, ৩... ভোটার নম্বর স্বয়ংক্রিয় অ্যাসাইনমেন্ট।
    [x] প্রতিটি অডিট ও প্রিভিউ তালিকায় stid, sessionyear এর সাথে বর্তমান ও স্থায়ী গ্রাম (previll, pervill) প্রদর্শন।
    [x] অডিট ও প্রিভিউ পপআপে Student ID-তে ক্লিক করলে সম্পূর্ণ শিক্ষার্থীর প্রোফাইল, ছবি, পিতা-মাতা, NID, মোবাইল, ঠিকানা এবং sessioninfo টেবিলের সমস্ত শিক্ষাবর্ষের এনরোলমেন্ট হিস্ট্রি সহ ইন্টারঅ্যাক্টিভ কুইক-ভিউ মডাল পপআপ।
    [x] শ্রেণিভিত্তিক ও সম্পূর্ণ প্রতিষ্ঠানের মাস্টার অভিভাবক ভোটার তালিকা (Electoral Roll) প্রিন্ট ভিউ।

27. mark-entry.php (Gradebook -> Marks Entry)

28. marks-distribution.php (Gradebook -> Marks Distribution)

29. merge-marks-custom.php (Gradebook -> Merge Marks)

30. notifications.php (Core -> Notifications)

31. payment-gateway.php (Payment -> Payment Gateway)

32. payment-settings-indivisual.php (Payment -> Indivisual Setup)
    [x] sessionyear, students টেবিল থেকে সিলেক্টেড শ্রেণি/শাখার শিক্ষার্থীর তালিকা রোল হিসাবে ড্রপডাউনে দেখাও। 
    [x] এটি কোন শিক্ষার্থীর ব্যক্তিগত কনসেসন সেটআপ।  আইটেমগুলোর ভ্যালু পরিবর্তন করা, আপডেট CRUD কনফার্ম করোা। 

33. payment-settings.php (Payment -> Payment Setup)

34. payments-collection.php (Payment -> Collection Report)

35. pgw-collection.php (Payment -> Gateway Collection)

36. progress-report.php (Gradebook -> Progress Report)

37. promotion-verifier.php (Tools -> Promotion Verifier)

38. registers-testimonials.php (Registers -> Testimonial)

39. result-processor.php (Gradebook -> Process Result)

40. result-report-manager.php (Gradebook -> Result Viewer)

41. settings-basic.php (Settings -> Basic Settings)
    [ ] --------------------

42. settings-gpa.php (Settings -> Grading System)

43. slot.php (Settings -> Slot / Unit)
    [x] অ্যকশন বাটনগুলো 3-ডট ড্রপডাউন মেনু দাও। 
    [x] ‍slots.sql অনুযায়ী টেবিল ও অ্যাড / এডিট পপআপ/মডাল রি-ডিজাইন করো, প্রয়োজনীয় স্ক্রিপ্ট পরিবর্তন করো। 

44. sms-gateway.php (Settings -> SMS Gateway)

45. sms-log.php (Authority -> SMS Log)

46. student-payable.php (Payment -> Self Payment [Student Payment])

47. students-list.php (Student -> Students List)
    [x] action dropdown menu (view profile) : student-view-profile.php তে letter head templete ব্যবহার করো। 
    একটা প্রিন্টেবল পুর্ণাঙ্গ প্রোফাইল তৈরী কর। students, sessioninfo টেবিল থেকে ডেটা আসবে। 
    Edit Profile লিংক আলাদা ট্যবে ওপেন হবে। 
    Id Card Menu টা আপাতত ডিসেবল করে রাখো। 
    [x] Waiver, tc, bonafied, overall-report মেনুগুলো মধ্যে waiver বাদ দাও। tc এর জন্য একটা পপআপ আসবে issues TC এর জন্র। এটা আমরা পরে পরিকল্পনা করবো। bonafied, overall-report  জন্র আলাদা টেমপ্লেট তৈরী করতে হবে, Letter head template ব্যবহার করে। ড্রপডাউনে মেনু সেট করতে হবে এগুলোর জন্য। Archive করলে কি হয়? আর্কাইভ করার পর, আর্কাইভড স্টুডেন্টদের ডেটা/তালিকা দেখার জন্য আলাদা স্ক্রিপ্ট চাই।   

48. students-payment.php (Payment -> Students Collection)

49. subject-manager.php (Academics -> Subject Manager)
    [x] Clone Default Subject List পপআপে ক্লাস ও সেকশনের ড্রপডাউনে সিলেক্ট ড্রপডাউন গুলো ক্রম এভাবে হবে : Session,  Global Default, Class, section . Global Defalul Yes/Default Subject list হলে class, section হবে, session, ‍sccode=0 দিয়ে subsetup টেবিল থেকে। নতুবা, শুধুমাত্র areas টেবিল থেকে sccode, ‍sessionyear অনুযায়ী class ও section দেখানো হবে। 
 
50. subjects-list.php (Academics -> Subjects List)
    [x] custom subject (institute itselt) code range 401-800
    [x] set 3 dot dropdown menu for action buttons in table.
    [x] hide ‍subject code >1000 for users (NOT ADMIN)
    [x] admin ছাড়া add subject কাজ করছে না


51. sync-payments.php (Payment -> Sync Payment)

52. tabulating-report.php (Gradebook -> Tabulation Sheet)

53. teacher-attendance-report.php (Teacher -> Teacher Attendance)
    [x] print/pdf এর হেডিং টা templete/letter head থেকে নাও। 

54. teacher-edit.php (Teacher -> Profile Editor)
    [x] teacher.sql অনুযায়ী সকল ডেটা আপডেট করার সুযোগ দাও।

55. teacher-view.php (Teacher -> Profile View)
    [x] teacher.sql অনুযায়ী সকল ডেটা দেখাও। 
    [x] teacher_salary_structure.sql থেকে শিক্ষকের সর্বশেষ বেতন স্ট্রাকচার দেখাও। শিক্ষকেদের বেতন স্ট্রাকচার বিভিন্ন সময় পরিবর্তন হতে পারে। 

56. teachers-list.php (Teacher -> Teachers List)
    [x] aDD teacher popup এ new tid জেনারেট ভুল হচ্ছে। tid জেনারেটের নিয়ম হবে, ‍6 digit sccode এর পর চার ডিজিট 9999 থেকে িএক এর করে নামতে থাকবে। 9999, 9998, 9997, এভাবে। 9501 -9999 পর্যন্ত শিক্ষক আইডি (tid) বরাদ্ধ থাকবে। নতুন আইডি জেনারেট করার জন্য সর্বনিম্ন শিক্ষক আইডি থেকে এক কম হবে।
    [x] add/edit teacher/staff popup এ slot এর পাশাপাশি designation / position দাও। ডেজিগনেশন এর তালিকা আসবে, designation.sql থেকে। ডেজিগনেশন ranks value >50 হলে তাকে staff হিসাবে দেখাবো। teacher টেবিলেও ranks কলাম আছে।
    [x] অ্যাকশন ড্রপডাউন মেনু থেকে কোন লিংক ওপেন করলে সেটা আরেকটা ট্যাবে ওপেন করো। 
    [x] teacher delete/remove করার মেনু নাই ড্রপডাউন মেনুতে। এটা সেট করো। 
    [x] Update Salary এর লিংক salary-settings.php সম্ভবত মিসিং। তৈরী করতে হবে। teacher_salary_structure.sql টেবিল।
    [x] attendance মেনুর লিংকটা মিসিং। নতুন স্ক্রিপ্ট বানাতে হবে। এটা নির্বাচিত শিক্ষকের হাজিরা দেখা যাবে। পুরো মাস, পুরো বছর। শিক্ষকের এই ইন্ডিভিজ্যুায়াল হাজিরা রিপোর্টে present, absent, late entry, leave ইত্যাদির হিসাব থাকবে। েইত্যাদি মার্ক করা থাকবে।  

57. user-profile.php (Core -> My Profile)

58. users-chat.php (Support -> Ticket)

59. users-list.php (User Management -> Users List)

60. 

61. 

62. 

63. 

64. 

65. 

66. 

67. 

68. 

69. 

70. 

71. 

72. 

73. 

74.75.76.77.78.79.80.81.82.83.84.85.86.87.88.89.90.91.92.93.94.95.96.97.98.99.100.





##  Admission System & Menu Less Script
1.  admit_card.php 



##  Backend & Administration/Developement
1.  admin_tickets.php (Backend -> Tickets)

2.  error-log.php (Developer -> Error Log)
3.  feature-tracker.php (Developer -> Feature Tracker)
4.  issue-tracker.php (Developer -> Issue Tracker)
5.  local-changes.php (Developer -> Log Auditor)
6.  merge-marks.php (Orion -> Merge Marks)
7.  module-manager.php (Developer -> Module Manager)
8.  module-structure.php (Developer -> Module Structure)
9.  monitoring-board.php (Backend -> Monitoring Board)
10. mysql-connection.php (Developer -> MySQL Monitor)
11. new-institute.php (Backend -> New Institute)
12. package-manager.php (Developer -> Package Manager)
13. package-mapper.php (Devoloper -> Package Mapper)
14. role-manager.php (Seed -> Role Manager)
15. view-ins-profile.php (Backend -> Institute Info)
16. 
17. 
18. 
19. 
20. 

