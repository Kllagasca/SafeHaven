<?php
require '../config/function.php';

if (isset($_GET['id'])) {
    $caseno = validate($_GET['id']);

    try {
        $query = "SELECT * FROM cases WHERE caseno = :caseno LIMIT 1";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':caseno', $caseno);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $case = $stmt->fetch(PDO::FETCH_ASSOC);

            $deleteQuery = "DELETE FROM cases WHERE caseno = :caseno";
            $deleteStmt = $pdo->prepare($deleteQuery);
            $deleteStmt->bindParam(':caseno', $caseno);

            if ($deleteStmt->execute()) {
                if (!empty($case['image']) && file_exists('../' . $case['image'])) {
                    unlink('../' . $case['image']);
                }
                redirect('cases.php', 'Case Deleted Successfully');
            } else {
                redirect('cases.php', 'Something Went Wrong');
            }

        } else {
            redirect('cases.php', 'Case Not Found');
        }

    } catch (PDOException $e) {
        redirect('cases.php', 'Error: ' . $e->getMessage());
    }

} else {
    redirect('cases.php', 'No Case ID Found');
}
