<?php include('includes/header.php'); ?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h4>
                    User Lists
                    <a href="user-create.php" class="btn btn-primary float-end">Add Users</a>
                </h4>
            </div>
            <div class="card-body">

            <?= alertmessage(); ?>

                <table id="myTable" class="table table-bordered table-striped text-center">
                    <thead>
                        <tr>
                            <th>User Id</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>

                        <?php 
                        // Fetch user data using PDO
                        $query = "SELECT * FROM users";
                        $stmt = $pdo->prepare($query);
                        $stmt->execute();
                        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

                        if ($users && count($users) > 0) {
                            foreach ($users as $userItem) {
                                ?>
                                    <tr>
                                        <td><?= htmlspecialchars($userItem['id']); ?></td>
                                        <td><?= htmlspecialchars($userItem['fname']); ?></td>
                                        <td><?= htmlspecialchars($userItem['lname']); ?></td>
                                        <td><?= htmlspecialchars($userItem['email']); ?></td>
                                        <td><?= htmlspecialchars($userItem['role']); ?></td>
                                        <td><?= $userItem['is_ban'] == 1 ? 'Banned' : 'Active'; ?></td>
                                        <td>
                                            <a href="user-edit.php?id=<?= $userItem['id']; ?>" class="btn btn-success btn-sm">Edit</a>
                                            <a href="user-delete.php?id=<?= $userItem['id']; ?>" class="btn btn-danger btn-sm mx-2" onclick="return confirm('Are you sure you want to delete this data?')">Delete</a>
                                        </td>
                                    </tr>
                                <?php
                            }
                        } else {
                            ?>
                            <tr>
                                <td colspan="7">No Record Found</td>
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