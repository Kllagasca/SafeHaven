<?php
include '../config/db_connect.php';

// Get the survey ID from the URL
$survey_id = $_GET['id'];

// Fetch the survey name for the specific survey_id
$surveyQuery = "SELECT name, description FROM surveys WHERE id = $survey_id";
$surveyResult = $conn->query($surveyQuery);
$survey = $surveyResult->fetch_assoc(); // Get survey data

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

<body class="bg-light">
    <div class="container mt-5">
        <!-- Logo Section -->
        <div class="text-center mb-4">
            <img src="../assets/img/logo.png" alt="Logo" class="img-fluid" style="max-width: 200px;">
        </div>

        <h1 class="text-center text-white"><?php echo htmlspecialchars($survey['name'], ENT_QUOTES, 'UTF-8'); ?></h1>
        <p class="text-center text-white mb-5"><?php echo htmlspecialchars($survey['description'], ENT_QUOTES, 'UTF-8'); ?></p>

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
            <div class="text-end mb-3">
                <a href="survey.php" class="btn text-decoration-none bg-primary text-white font-weight-bold">
                <i class="fas fa-arrow-left" style="margin-right: 5px;"></i> Back to Surveys
                </a>
            </div>

<div class="rounded text-center mx-auto" id="container-<?php echo $question->id; ?>" style="width: 100%;"></div>

                <p class="highcharts-description text-center mt-3">
                    Responses for: <?php echo htmlspecialchars($question->question, ENT_QUOTES, 'UTF-8'); ?>
                </p>
            </figure>

            <script>
                Highcharts.chart('container-<?php echo $question->id; ?>', {
                    chart: {
                        type: 'pie'
                    },
                    title: {
                        text: 'Question: <?php echo addslashes($question->question); ?>'
                    },
                    tooltip: {
                        valueSuffix: '%'
                    },
                    subtitle: {
                        text: 'Source: <a href="../index.php" target="_default">GenderDev</a>'
                    },
                    plotOptions: {
                        series: {
                            allowPointSelect: true,
                            cursor: 'pointer',
                            dataLabels: [{
                                enabled: true,
                                distance: 20
                            }, {
                                enabled: true,
                                distance: -40,
                                format: '{point.percentage:.1f}%',
                                style: {
                                    fontSize: '1.2em',
                                    textOutline: 'none',
                                    opacity: 0.7
                                },
                                filter: {
                                    operator: '>',
                                    property: 'percentage',
                                    value: 10
                                }
                            }]
                        }
                    },
                    series: [{
                        name: 'Answer',
                        colorByPoint: true,
                        data: [
                            <?php echo implode(',', $responseData); ?>
                        ]
                    }]
                });
            </script>
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
