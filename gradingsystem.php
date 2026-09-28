<?php

$courseList = array("BSIT", "BSCS", "BSIS", "ACT");

$subjectMap = array(
    "grade_web"    => "Web Programming",
    "grade_db"     => "Database Systems",
    "grade_sysan"  => "Systems Analysis",
    "grade_mobile" => "Mobile Development"
);

$problems = array();
$report = null;

$formData = array("sid" => "", "sname" => "", "course" => "");
foreach ($subjectMap as $field => $title) {
    $formData[$field] = "";
}

function sanitize($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $formData["sid"]    = isset($_POST["sid"]) ? trim($_POST["sid"]) : "";
    $formData["sname"]  = isset($_POST["sname"]) ? trim($_POST["sname"]) : "";
    $formData["course"] = isset($_POST["course"]) ? $_POST["course"] : "";

    if ($formData["sid"] === "") {
        $problems[] = "Please enter the student ID.";
    }

    if ($formData["sname"] === "") {
        $problems[] = "Please enter the student's full name.";
    } elseif (!preg_match("/^[a-zA-Z .,'-]+$/", $formData["sname"])) {
        $problems[] = "The name can only have letters, spaces and basic punctuation.";
    }

    if (!in_array($formData["course"], $courseList)) {
        $problems[] = "Please choose a program from the list.";
    }

    $gradeBook = array();
    foreach ($subjectMap as $field => $title) {
        $entered = isset($_POST[$field]) ? trim($_POST[$field]) : "";
        $formData[$field] = $entered;

        if ($entered === "") {
            $problems[] = "The grade for $title is missing.";
        } elseif (!is_numeric($entered) || $entered < 0 || $entered > 100) {
            $problems[] = "The grade for $title must be between 0 and 100.";
        } else {
            $gradeBook[$title] = (float)$entered;
        }
    }

    if (count($problems) === 0) {

        $sum = 0;
        foreach ($gradeBook as $score) {
            $sum = $sum + $score;
        }
        $mean = round($sum / count($gradeBook), 2);

        if ($mean >= 90) {
            $letterGrade = "A";
            $verdict = "Excellent";
        } elseif ($mean >= 85) {
            $letterGrade = "B";
            $verdict = "Very Good";
        } elseif ($mean >= 80) {
            $letterGrade = "C";
            $verdict = "Good";
        } elseif ($mean >= 75) {
            $letterGrade = "D";
            $verdict = "Passed";
        } else {
            $letterGrade = "F";
            $verdict = "Failed";
        }

        if ($mean >= 90) {
            $standing = "Dean's List Candidate";
        } elseif ($mean >= 85) {
            $standing = "Honor Student Candidate";
        } elseif ($mean >= 75) {
            $standing = "Regular Student";
        } else {
            $standing = "Academic Intervention Required";
        }

        $report = array(
            "sid"      => $formData["sid"],
            "sname"    => $formData["sname"],
            "course"   => $formData["course"],
            "scores"   => $gradeBook,
            "mean"     => $mean,
            "letter"   => $letterGrade,
            "verdict"  => $verdict,
            "standing" => $standing
        );
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Grade Management System</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: "Trebuchet MS", Verdana, sans-serif;
            background: #0f2d33;
            margin: 0;
            padding: 32px 14px;
            color: #1f2d2f;
        }
        .page { max-width: 680px; margin: 0 auto; }
        .title {
            text-align: center;
            color: #f4e9c8;
            letter-spacing: 1px;
            margin: 0 0 4px;
        }
        .subtitle {
            text-align: center;
            color: #8fb8b5;
            margin: 0 0 26px;
            font-size: .9rem;
        }
        .panel {
            background: #fbf8f0;
            border-top: 6px solid #e0a43a;
            border-radius: 8px;
            padding: 22px 24px;
            margin-bottom: 22px;
        }
        .panel h2 {
            margin: 0 0 6px;
            font-size: 1.1rem;
            color: #12474f;
        }
        label {
            display: block;
            margin: 14px 0 5px;
            font-size: .88rem;
            font-weight: bold;
            color: #12474f;
        }
        input, select {
            width: 100%;
            padding: 10px 12px;
            border: 2px solid #cfd8d3;
            border-radius: 5px;
            font-size: 1rem;
            background: #fff;
        }
        input[type=number] { -moz-appearance: textfield; appearance: textfield; }
        input[type=number]::-webkit-outer-spin-button,
        input[type=number]::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        input:focus, select:focus {
            outline: none;
            border-color: #e0a43a;
        }
        .two-col {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0 18px;
        }
        .submit-btn {
            margin-top: 22px;
            width: 100%;
            padding: 12px;
            background: #12474f;
            color: #f4e9c8;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
        }
        .submit-btn:hover { background: #1b6069; }
        .profile { display: flex; flex-wrap: wrap; gap: 10px 30px; margin-bottom: 14px; }
        .profile div span {
            display: block;
            font-size: .75rem;
            text-transform: uppercase;
            color: #6c8580;
        }
        .profile div strong { font-size: 1.05rem; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0 18px; }
        th, td { padding: 9px 10px; text-align: left; border-bottom: 1px solid #e4ded0; }
        th { background: #12474f; color: #f4e9c8; font-size: .85rem; }
        td.num { text-align: right; font-weight: bold; }
        .stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; }
        .stat {
            background: #12474f;
            color: #f4e9c8;
            border-radius: 6px;
            padding: 14px;
            text-align: center;
        }
        .stat span {
            display: block;
            font-size: .72rem;
            text-transform: uppercase;
            color: #9cc7c3;
            margin-bottom: 4px;
        }
        .stat strong { font-size: 1.5rem; }
        .stat.good { background: #2f7d4f; }
        .stat.bad { background: #b03a2e; }
        .standing {
            margin-top: 14px;
            padding: 12px;
            text-align: center;
            background: #f4e9c8;
            border: 2px dashed #e0a43a;
            border-radius: 6px;
            font-weight: bold;
            color: #12474f;
        }
        @media (max-width: 520px) {
            .two-col, .stats { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<div class="page">
    <h1 class="title">Student Grade Management System</h1>
    <p class="subtitle">Enter the grades to see the average, remarks and classification</p>

    <div class="panel">
        <h2>Student Details</h2>

        <form method="post" action="">
            <label for="sid">Student ID</label>
            <input type="text" id="sid" name="sid" value="<?php echo sanitize($formData['sid']); ?>" required>

            <label for="sname">Student Name</label>
            <input type="text" id="sname" name="sname" value="<?php echo sanitize($formData['sname']); ?>" required>

            <label for="course">Program</label>
            <select id="course" name="course" required>
                <option value="">Select a program</option>
                <?php foreach ($courseList as $c): ?>
                    <option value="<?php echo sanitize($c); ?>" <?php echo ($formData["course"] === $c) ? "selected" : ""; ?>>
                        <?php echo sanitize($c); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <div class="two-col">
                <?php foreach ($subjectMap as $field => $title): ?>
                    <div>
                        <label for="<?php echo sanitize($field); ?>"><?php echo sanitize($title); ?></label>
                        <input type="number" id="<?php echo sanitize($field); ?>" name="<?php echo sanitize($field); ?>"
                               min="0" max="100" step="0.01" value="<?php echo sanitize($formData[$field]); ?>" required>
                    </div>
                <?php endforeach; ?>
            </div>

            <button type="submit" class="submit-btn">Show Result</button>
        </form>
    </div>

    <?php if ($report !== null): ?>
        <div class="panel">
            <h2>Grade Report</h2>

            <div class="profile">
                <div><span>Name</span><strong><?php echo sanitize($report["sname"]); ?></strong></div>
                <div><span>Student ID</span><strong><?php echo sanitize($report["sid"]); ?></strong></div>
                <div><span>Course / Program</span><strong><?php echo sanitize($report["course"]); ?></strong></div>
            </div>

            <table>
                <tr><th>Subject</th><th style="text-align:right;">Grade</th></tr>
                <?php foreach ($report["scores"] as $subject => $score): ?>
                    <tr>
                        <td><?php echo sanitize($subject); ?></td>
                        <td class="num"><?php echo sanitize($score); ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>

            <div class="stats">
                <div class="stat">
                    <span>Average</span>
                    <strong><?php echo number_format($report["mean"], 2); ?></strong>
                </div>
                <div class="stat">
                    <span>Letter Equivalent</span>
                    <strong><?php echo sanitize($report["letter"]); ?></strong>
                </div>
                <div class="stat <?php echo ($report['letter'] === 'F') ? 'bad' : 'good'; ?>">
                    <span>Remarks</span>
                    <strong><?php echo sanitize($report["verdict"]); ?></strong>
                </div>
            </div>

            <div class="standing">
                Academic Classification: <?php echo sanitize($report["standing"]); ?>
            </div>
        </div>
    <?php endif; ?>
</div>
</body>
</html>