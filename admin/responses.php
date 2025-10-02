<?php
include '../config/db_connect.php';

// Get the survey ID from the URL
$survey_id = $_GET['id'];

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    die("Invalid survey ID");
}

// Fetch the survey name for the specific survey_id
$surveyQuery = "SELECT name, description FROM surveys WHERE id = $survey_id";
$surveyResult = $conn->query($surveyQuery);
$survey = $surveyResult->fetch_assoc(); // Get survey data

$totalQuery = "
    SELECT COUNT(*) as total 
    FROM responses 
    WHERE question_id IN (
        SELECT id FROM questions 
        WHERE section_id IN (
            SELECT id FROM sections WHERE survey_id = $id
        )
    )
";
$totalResult = mysqli_query($conn, $totalQuery);
$totalResponses = mysqli_fetch_assoc($totalResult)['total'];

$totalQuestionsQuery = "
    SELECT COUNT(*) as total_questions
    FROM questions
    WHERE section_id IN (
        SELECT id FROM sections WHERE survey_id = $id
    )
";
$totalQuestionsResult = mysqli_query($conn, $totalQuestionsQuery);
$totalQuestions = mysqli_fetch_assoc($totalQuestionsResult)['total_questions'];

$trendQuery = "
    SELECT DATE(created_at) as date, COUNT(*) as count 
    FROM responses r
    JOIN questions q ON r.question_id = q.id
    JOIN sections s ON q.section_id = s.id
    WHERE s.survey_id = $id
    GROUP BY DATE(created_at)
    ORDER BY date ASC
";
$trendResult = mysqli_query($conn, $trendQuery);
$trendData = mysqli_fetch_all($trendResult, MYSQLI_ASSOC);


// Fetch questions for the specific survey
$questionsQuery = "SELECT id, question FROM questions WHERE survey_id = $survey_id";
$questionsResult = $conn->query($questionsQuery);
$questions = [];
if ($questionsResult->num_rows > 0) {
    while ($row = $questionsResult->fetch_object()) {
        $questions[] = $row;
    }
}
?>

<head>
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/modules/exporting.js"></script>
    <script src="https://code.highcharts.com/modules/accessibility.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

    <div class="text-center mb-4">
            <img src="../assets/img/logo.png" alt="Logo" class="img-fluid" style="max-width: 200px;">
        </div>

        <h1 class="text-center text-white"><?php echo htmlspecialchars($survey['name'], ENT_QUOTES, 'UTF-8'); ?></h1>
        <p class="text-center text-white mb-5"><?php echo htmlspecialchars($survey['description'], ENT_QUOTES, 'UTF-8'); ?></p>
<body class="bg-light">
    <div class="container mt-5">

    <div class="text-end mb-3">
        <a href="survey.php" class="btn text-decoration-none bg-primary text-white font-weight-bold">
            <i class="fas fa-arrow-left" style="margin-right: 5px;"></i> Back to Surveys
        </a>
    </div>

        <!-- Logo Section -->
        <?php foreach ($questions as $question): ?>
            <?php
            // Fetch response counts for each answer to this question
            $responsesQuery = "
                SELECT answer, COUNT(*) as count 
                FROM responses 
                WHERE question_id = {$question->id} 
                GROUP BY answer
            ";
            $responsesResult = $conn->query($responsesQuery);
            $responseData = [];
            if ($responsesResult->num_rows > 0) {
                while ($row = $responsesResult->fetch_object()) {
                    $responseData[] = "{ name: '" . addslashes($row->answer) . "', y: " . $row->count . " }";
                }
            }
            ?>


            <!-- Back Button -->

            <!-- Create Chart for Each Question -->
            <figure class="highcharts-figure survey-container p-5 text-white rounded shadow-sm mb-3" style="background-color:rgba(0, 0, 0, 0.51);">

    <?php
    // === safe rendering per question ===
    $qid = intval($question->id);

    // detect type
    $type = 'choice';
    if (!empty($question->type)) {
        $type = $question->type;
    } else {
        $qtext = strtolower($question->question);
        if (strpos($qtext, 'email') !== false || strpos($qtext, 'gmail') !== false || strpos($qtext, 'name') !== false) {
            $type = 'text';
        }
    }

    // fetch responses
    $responses = [];
    $sql = "SELECT answer, created_at FROM responses WHERE question_id = $qid";
    $res = mysqli_query($conn, $sql);
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $responses[] = $row;
        }
    }

    if (empty($responses)) {
        echo '<p class="text-center"><em>No responses yet.</em></p>';
    } else {
        if ($type === 'choice') {
            $counts = [];
            foreach ($responses as $r) {
                $ans = (string)$r['answer'];
                if ($ans === '') $ans = '(No answer)';
                $counts[$ans] = ($counts[$ans] ?? 0) + 1;
            }
            $responseDataParts = [];
            foreach ($counts as $ans => $count) {
                $responseDataParts[] = json_encode(['name' => $ans, 'y' => (int)$count]);
            }
            ?>
            <div id="container-<?php echo $qid; ?>" style="min-width:300px; height:400px; margin: 0 auto;"></div>
            <script>
            Highcharts.chart('container-<?php echo $qid; ?>', {
                chart: { type: 'pie' },
                title: { text: <?php echo json_encode("Question: " . $question->question); ?> },
                subtitle: { text: 'Source: YourSurvey' },
                tooltip: { pointFormat: '<b>{point.percentage:.1f}%</b> ({point.y})' },
                plotOptions: {
                    pie: {
                        allowPointSelect: true,
                        cursor: 'pointer',
                        dataLabels: { enabled: true, format: '{point.name}: {point.percentage:.1f} %' }
                    }
                },
                series: [{ name: 'Answers', colorByPoint: true, data: [<?php echo implode(',', $responseDataParts); ?>] }]
            });
            </script>
            <?php
        } else {
            echo '<div style=" padding:15px; border-radius:8px; margin-bottom:20px;">';
            echo '<h4 style="color:#fff;">' . htmlspecialchars($question->question) . '</h4>';
            echo '<ul style="list-style-type: disc; padding-left:20px; color:#fff;">';
            foreach ($responses as $r) {
                echo '<li>' . htmlspecialchars($r['answer']) . '</li>';
            }
            echo '</ul>';
            echo '</div>';
        }

        
    }
    ?>
</figure>




        <?php endforeach; ?>
    </div>

    <style>

::-webkit-scrollbar {
            display: none;
        }
        html {
            scrollbar-width: none;
        }
        .bg-light {
            background-image: url('../assets/img/survey-bg.png');
            background-size: cover;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }
        .survey-container {
            font-family: Montserrat, sans-serif;
        }
        .highcharts-description {
            color: #fff;
            font-size: 1.1em;
        }
    </style>
</body>