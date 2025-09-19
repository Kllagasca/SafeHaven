<?php include('includes/header.php'); ?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-5">
                        <h4>Cases</h4>
                    </div>
                    <div class="col-md-7">
                        <!-- Add Case Button -->
                        <a href="case-create.php" class="btn btn-primary float-end">Add New Case</a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <?= alertmessage(); ?>

                <table id="myTable" class="table table-bordered table-striped text-center">
                    <thead>
                        <tr>
                            <th>Case No.</th>
                            <th>Case Title</th>
                            <th>Incident Location</th>
                            <th>Date of Incident</th>
                            <th>Complainant Name</th>
                            <th>Case Details</th>
                            <th>Case Status</th>
                            <th>Case Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = "SELECT * FROM cases";
                        $stmt = $pdo->prepare($query);
                        $stmt->execute();
                        $cases = $stmt->fetchAll(PDO::FETCH_ASSOC);

                        foreach ($cases as $item) {
                        ?>
                        <tr>
                            <td class="doc-title"><?= htmlspecialchars($item['caseno']); ?></td>
                            <td class="doc-title"><?= htmlspecialchars($item['title']); ?></td>
                            <td class="doc-title"><?= htmlspecialchars($item['brgy']); ?></td>
                            <td class="doc-title"><?= htmlspecialchars($item['date']); ?></td>
                            <td class="doc-title"><?= htmlspecialchars($item['comp_name']); ?></td>
                            <td class="doc-title"><?= htmlspecialchars(strip_tags($item['long_description'])); ?></td>
                            <td><?= htmlspecialchars($item['status']); ?></td>
                            <td>
                            <a href="case-edit.php?caseno=<?= urlencode($item['caseno']); ?>" class="btn btn-primary btn-sm">Edit</a>

                                <a href="case-delete.php?id=<?= $item['caseno']; ?>" 
                                   class="btn btn-danger btn-sm"
                                   onclick="return confirm('Are you sure you want to delete this case?')">Delete</a>
                            </td>
                        </tr>
                        <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>

<style>
    #myTable th, #myTable td {
        white-space: nowrap;
    }
    #myTable .doc-title {
        max-width: 100px;
        white-space: normal;
        word-wrap: break-word;
    }
</style>
