<?php
$page_title = "Manage Polls";
include 'includes/header.php';

// শুধুমাত্র অ্যাডমিন
if (!isset($_SESSION['user_id']) || $is_admin===0) {
    echo "<div class='alert alert-danger text-center mt-4'>Access denied. Admins only.</div>";
    include 'includes/footer.php';
    exit;
}

$message = "";

// Poll ফর্ম সাবমিট
if (isset($_POST['question'], $_POST['options'])) {
    $question = trim($_POST['question']);
    $options = array_filter(array_map('trim', $_POST['options'])); // remove empty

    if (!empty($question) && count($options) >= 2) {
        if (isset($_POST['poll_id']) && $_POST['poll_id'] > 0) {
            // Update existing poll
            $poll_id = intval($_POST['poll_id']);
            $stmt = $conn->prepare("UPDATE polls SET question=? WHERE id=?");
            $stmt->bind_param("si", $question, $poll_id);
            $stmt->execute();

            // Delete old options
            $conn->query("DELETE FROM poll_options WHERE poll_id=$poll_id");

            // Insert new options
            $stmt2 = $conn->prepare("INSERT INTO poll_options (poll_id, option_text) VALUES (?, ?)");
            foreach ($options as $opt) {
                $stmt2->bind_param("is", $poll_id, $opt);
                $stmt2->execute();
            }
            $message = "<div class='alert alert-success'>✅ Poll updated successfully.</div>";
        } else {
            // Insert new poll
            $stmt = $conn->prepare("INSERT INTO polls (question, created_at) VALUES (?, NOW())");
            $stmt->bind_param("s", $question);
            $stmt->execute();
            $poll_id = $conn->insert_id;

            // Insert options
            $stmt2 = $conn->prepare("INSERT INTO poll_options (poll_id, option_text) VALUES (?, ?)");
            foreach ($options as $opt) {
                $stmt2->bind_param("is", $poll_id, $opt);
                $stmt2->execute();
            }
            $message = "<div class='alert alert-success'>✅ New poll created.</div>";
        }
    } else {
        $message = "<div class='alert alert-warning'>Please enter a question and at least 2 options.</div>";
    }
}

// Delete poll
if (isset($_GET['delete']) && $_GET['delete'] > 0) {
    $delete_id = intval($_GET['delete']);
    $conn->query("DELETE FROM polls WHERE id=$delete_id");
    $conn->query("DELETE FROM poll_options WHERE poll_id=$delete_id");
    $conn->query("DELETE FROM poll_votes WHERE poll_id=$delete_id");
    $message = "<div class='alert alert-success'>✅ Poll deleted.</div>";
}

// Load all polls
$pollsRes = $conn->query("SELECT * FROM polls ORDER BY created_at DESC");
?>

<div class="container my-4">
    <h2 class="mb-4 text-center">📊 Poll Management</h2>
    <?= $message ?>

    <div class="card mb-4">
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="poll_id" id="poll_id" value="">
                <div class="mb-3">
                    <input type="text" name="question" id="poll_question" class="form-control" placeholder="Poll Question" required>
                </div>
                <div id="options_wrapper">
                    <div class="mb-2"><input type="text" name="options[]" class="form-control mb-1" placeholder="Option 1" required></div>
                    <div class="mb-2"><input type="text" name="options[]" class="form-control mb-1" placeholder="Option 2" required></div>
                </div>
                <button type="button" class="btn btn-sm btn-secondary mb-2" id="add_option">+ Add Option</button>
                <button type="submit" class="btn btn-success w-100">Save Poll</button>
            </form>
        </div>
    </div>

    <h4>Existing Polls</h4>
    <table class="table table-striped table-bordered">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Question</th>
                <th>Options</th>
                <th>Votes</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($poll = $pollsRes->fetch_assoc()): 
                $optionsRes = $conn->query("SELECT * FROM poll_options WHERE poll_id={$poll['id']}");
                $votesRes = $conn->query("SELECT COUNT(*) as cnt FROM poll_votes WHERE poll_id={$poll['id']}");
                $totalVotes = $votesRes->fetch_assoc()['cnt'];
            ?>
                <tr>
                    <td><?= $poll['id'] ?></td>
                    <td><?= htmlspecialchars($poll['question']) ?></td>
                    <td>
                        <ul>
                        <?php while ($opt = $optionsRes->fetch_assoc()): ?>
                            <li><?= htmlspecialchars($opt['option_text']) ?></li>
                        <?php endwhile; ?>
                        </ul>
                    </td>
                    <td><?= $totalVotes ?></td>
                    <td>
                        <button class="btn btn-sm btn-primary edit-btn"
                            data-id="<?= $poll['id'] ?>"
                            data-question="<?= htmlspecialchars($poll['question'], ENT_QUOTES) ?>"
                            data-options='<?= json_encode(array_column($conn->query("SELECT option_text FROM poll_options WHERE poll_id={$poll['id']}")->fetch_all(MYSQLI_ASSOC), "option_text")) ?>'
                        >Edit</button>
                        <a href="?delete=<?= $poll['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this poll?')">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<script>
// Add option
document.getElementById('add_option').addEventListener('click', function(){
    let wrapper = document.getElementById('options_wrapper');
    let count = wrapper.querySelectorAll('input').length + 1;
    let input = document.createElement('input');
    input.type = 'text';
    input.name = 'options[]';
    input.className = 'form-control mb-1';
    input.placeholder = 'Option ' + count;
    wrapper.appendChild(input);
});

// Edit poll
document.querySelectorAll('.edit-btn').forEach(btn => {
    btn.addEventListener('click', function(){
        let pollId = this.dataset.id;
        let question = this.dataset.question;
        let options = JSON.parse(this.dataset.options);

        document.getElementById('poll_id').value = pollId;
        document.getElementById('poll_question').value = question;

        let wrapper = document.getElementById('options_wrapper');
        wrapper.innerHTML = '';
        options.forEach((opt, i) => {
            let input = document.createElement('input');
            input.type = 'text';
            input.name = 'options[]';
            input.className = 'form-control mb-1';
            input.placeholder = 'Option ' + (i+1);
            input.value = opt;
            wrapper.appendChild(input);
        });
        window.scrollTo({top:0, behavior:'smooth'});
    });
});
</script>

<?php include 'includes/footer.php'; ?>
