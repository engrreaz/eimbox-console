<?php 
header('Location:dashboard.php');
?>

<!doctype html>
<html lang="en">

<head>
    <title>Title</title>
    <!-- Required meta tags -->
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />

    <!-- Bootstrap CSS v5.2.1 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous" />
</head>

<body>
    <header>
        <!-- place navbar here -->
    </header>
    <main>

        <div id="one">
            <input type="text" id="class" value="Six" />
            <br>
            <input type="date" id="dob" value="2025-11-07" />
            <br>
            <input type="color" id="bgcolor" value="#889900" />
            <br>
            <input type="number" id="amount" value="7000" />
            <br>
            <input type="tel" id="mobile" value="01919629672" />
        </div>

        <div id="two">
            <label><input type="radio" name="gender" value="male"> Male</label><br>
            <label><input type="radio" name="gender" value="female"> Female</label><br>
            <label><input type="radio" name="gender" value="other"> Other</label><br>

            <!-- Checkboxes -->
            <p>Select your hobbies:</p>
            <label><input type="checkbox" name="hobby" value="reading"> Reading</label><br>
            <label><input type="checkbox" name="hobby2" value="traveling"> Traveling</label><br>

            <!-- Select dropdown -->
            <p>Select your country:</p>
            <select name="country">
                <option value="">--Select--</option>
                <option value="bd">Bangladesh</option>
                <option value="in">India</option>
                <option value="us">USA</option>
            </select>
        </div>

        <button onclick="go();">Go</button>



    </main>
    <footer>
        <!-- place footer here -->
    </footer>
    <!-- Bootstrap JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
        integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r"
        crossorigin="anonymous"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"
        integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+"
        crossorigin="anonymous"></script>


    <script>
        function getDivInputsAsParams(divId) {
            let div = document.getElementById(divId);
            let inputs = div.querySelectorAll("input, select, textarea"); // সব input element
            let params = [];

            inputs.forEach(input => {
                let name = input.name || input.id || ""; // param name
                let value = "";

                if (input.type === "checkbox" || input.type === "radio") {
                    if (input.checked) {
                        value = input.value;
                    } else {
                        return; // unchecked হলে skip
                    }
                } else {
                    value = input.value;
                }

                if (name) {
                    params.push(encodeURIComponent(name) + "=" + encodeURIComponent(value));
                }
            });

            return params.join("&");
        }

    </script>


    <script>
        function go() {
            let params1 = getDivInputsAsParams("one");
            let params2 = getDivInputsAsParams("two");

            let finalParams = params1 + "&" + params2;

            let targetUrl = "?" + finalParams;

            console.log(targetUrl);
            // Example: save.php?student_name=Reazul&dob=2025-09-18&class_id=2&section=A
            window.location.href = targetUrl;
        }
    </script>
</body>

</html>