<?php
// export_subjects.php – Exports subjects as XML, optionally filtered by faculty
$directCall = realpath(__FILE__) === realpath($_SERVER['SCRIPT_FILENAME']);

if (PHP_SAPI === 'cli') {
    $facultyId = isset($argv[1]) ? (int)$argv[1] : 0;
} else {
    $facultyId = isset($_GET['faculty_id']) ? (int)$_GET['faculty_id'] : 0;
}

if ($directCall) {
    header('Content-Type: application/xml; charset=UTF-8');
    $fileName = $facultyId
        ? "subjects_faculty_{$facultyId}.xml"
        : "subjects_all_faculties.xml";
    header("Content-Disposition: attachment; filename=\"{$fileName}\"");
}

echo '<?xml version="1.0" encoding="UTF-8"?>', "\n";
echo "<subjects>\n";

try {
    $pdo = new PDO(
        "pgsql:host=localhost;port=5432;dbname=bibliobros",
        "postgres","klk",
        [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]
    );

    $sql = "
      SELECT s.id   AS subject_id,
             s.name AS subject_name,
             f.id   AS faculty_id,
             f.name AS faculty_name,
             u.name AS university_name
      FROM subjects s
      JOIN faculties  f ON s.faculty_id    = f.id
      JOIN universities u ON f.university_id = u.id
      ".($facultyId ? "WHERE f.id = :fid\n" : "")."
      ORDER BY u.name, f.name, s.name;
    ";
    $stmt = $pdo->prepare($sql);
    if ($facultyId) {
        $stmt->bindValue('fid', $facultyId, PDO::PARAM_INT);
    }
    $stmt->execute();

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $id   = (int) $row['subject_id'];
        $name = htmlspecialchars($row['subject_name'],  ENT_XML1|ENT_QUOTES, 'UTF-8');
        $fid  = (int) $row['faculty_id'];
        $fac  = htmlspecialchars($row['faculty_name'],  ENT_XML1|ENT_QUOTES, 'UTF-8');
        $uni  = htmlspecialchars($row['university_name'],ENT_XML1|ENT_QUOTES, 'UTF-8');

        echo "  <subject>\n",
             "    <id>{$id}</id>\n",
             "    <name>{$name}</name>\n",
             "    <faculty id=\"{$fid}\">{$fac}</faculty>\n",
             "    <university>{$uni}</university>\n",
             "  </subject>\n";
    }
} catch (PDOException $e) {
    $msg = htmlspecialchars($e->getMessage(), ENT_XML1|ENT_QUOTES, 'UTF-8');
    echo "  <error>{$msg}</error>\n";
}

echo "</subjects>";
