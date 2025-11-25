<?php 
session_start();
if (isset($_SESSION['admin_id']) && 
    isset($_SESSION['role'])) {

    if ($_SESSION['role'] == 'Admin') {
       include "../DB_connection.php";
       include "data/teacher.php";
       include "data/subject.php";
       include "data/grade.php";
       include "data/class.php";
       include "data/section.php";
       $teachers = getAllTeachers($conn);
       
       // Build SWE subject IDs dynamically from subjects table
       $swe_subject_ids = [];
       $allSubjects = getAllSubjects($conn);
       if ($allSubjects != 0) {
           foreach ($allSubjects as $subj) {
               $hay = strtolower($subj['subject'].' '.$subj['subject_code']);
               if (strpos($hay, 'swe') !== false ||
                   strpos($hay, 'software') !== false ||
                   strpos($hay, 'softwear') !== false ||
                   strpos($hay, 'program') !== false ||
                   strpos($hay, 'java') !== false) {
                   $swe_subject_ids[] = (string)$subj['subject_id'];
               }
           }
       }
       // Fallback to Programming (6) and Java (7) if nothing matched
       if (empty($swe_subject_ids)) {
           $swe_subject_ids = ['6','7'];
       }

       $filtered_teachers = [];
       foreach ($teachers as $teacher) {
           $subjects = str_split(trim($teacher['subjects']));
           if (!empty($subjects)) {
               $onlySwe = count(array_diff($subjects, $swe_subject_ids)) == 0;
               $hasSwe  = count(array_intersect($subjects, $swe_subject_ids)) > 0;
               if ($onlySwe && $hasSwe) {
                   $filtered_teachers[] = $teacher;
               }
           }
       }
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Admin - SWE Teachers</title>
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css">
	<link rel="stylesheet" href="../css/style.css">
	<link rel="icon" href="../logo.png">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body>
    <?php 
        include "inc/navbar.php";
        if (!empty($filtered_teachers)) {
     ?>
     <div class="container mt-5">
        <h3>SWE Teachers (Software/Programming/Java)</h3>
        <div class="table-responsive">
          <table class="table table-bordered mt-3 n-table">
            <thead>
              <tr>
                <th scope="col">#</th>
                <th scope="col">ID</th>
                <th scope="col">First Name</th>
                <th scope="col">Last Name</th>
                <th scope="col">Username</th>
                <th scope="col">Subject(s)</th>
                <th scope="col">Action</th>
              </tr>
            </thead>
            <tbody>
              <?php $i = 0; foreach ($filtered_teachers as $teacher ) { 
                $i++;  ?>
              <tr>
                <th scope="row"><?=$i?></th>
                <td><?=$teacher['teacher_id']?></td>
                <td><a href="teacher-view.php?teacher_id=<?=$teacher['teacher_id']?>">
                     <?=$teacher['fname']?></a></td>
                <td><?=$teacher['lname']?></td>
                <td><?=$teacher['username']?></td>
                <td>
                   <?php 
                       $s = '';
                       $subjects = str_split(trim($teacher['subjects']));
                       foreach ($subjects as $subject) {
                          $s_temp = getSubjectById($subject, $conn);
                          if ($s_temp != 0) 
                            $s .=$s_temp['subject_code'].', ';
                       }
                       echo rtrim($s, ', ');
                    ?>
                </td>
                <td>
                    <a href="teacher-edit.php?teacher_id=<?=$teacher['teacher_id']?>"
                       class="btn btn-warning">Edit</a>
                    <a href="teacher-delete.php?teacher_id=<?=$teacher['teacher_id']?>"
                       class="btn btn-danger">Delete</a>
                </td>
              </tr>
            <?php } ?>
            </tbody>
          </table>
        </div>
     </div>
     <?php }else{ ?>
         <div class="alert alert-info .w-450 m-5" 
              role="alert">
            No SWE teachers found!
          </div>
     <?php } ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js"></script>  
    <script>
        $(document).ready(function(){
             $("#navLinks li a[href='swe-teachers.php']").addClass('active');
        });
    </script>
</body>
</html>
<?php 
  }else {
    header("Location: ../login.php");
    exit;
  } 
}else {
	header("Location: ../login.php");
	exit;
} 
?>
