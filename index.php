<?php
require 'dompdf/autoload.inc.php';

if (array_key_exists('export_html', $_POST) && array_key_exists('edited_file', $_POST)) {
    $edited_file = $_POST['edited_file'];
    export_to_html($edited_file);
}

if (array_key_exists('export_pdf', $_POST) && array_key_exists('edited_file', $_POST)) {
    $edited_file = $_POST['edited_file'];
    export_to_pdf($edited_file);
}

if (array_key_exists('create_file', $_POST)) {
    if (array_key_exists('file_name', $_POST)) {
        create_file($_POST['file_name']);
    } else {
        create_file();
    }
}

display_files();

function export_to_html($file_name)
{
    $file_path = "files/" . $file_name;
    $output_path = "exports/" . $file_name . ".html";

    if (file_exists($file_path)) {
        if (strtolower(pathinfo($file_path, PATHINFO_EXTENSION)) === 'mywac') {
            $content = file_get_contents($file_path);
            file_put_contents($output_path, $content);

            echo "Exportation en HTML réussie! <a href='download.php?file=$output_path&type=html'>Télécharger le fichier</a>";
        } else {
            echo "La création et l’édition des fichiers en ligne ne sont possibles que pour les fichiers de type .mywac";
        }
    } else {
        echo "Le fichier n'existe pas.";
    }
}

function export_to_pdf($file_name)
{
    $file_path = "files/" . $file_name;
    $output_path = "exports/" . $file_name . ".pdf";

    if (file_exists($file_path)) {
        if (strtolower(pathinfo($file_path, PATHINFO_EXTENSION)) === 'mywac') {
            $content = file_get_contents($file_path);

            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($content);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            file_put_contents($output_path, $dompdf->output());

            echo "Exportation en PDF réussie! <a href='download.php?file=$output_path&type=pdf'>Télécharger le fichier</a>";
        } else {
            echo "La création et l’édition des fichiers en ligne ne sont possibles que pour les fichiers de type .mywac";
        }
    } else {
        echo "Le fichier n'existe pas.";
    }
}

if (array_key_exists('save_changes', $_POST) && array_key_exists('edited_file', $_POST)) {
    $edited_file = $_POST['edited_file'];
    $new_content = $_POST['file_content'];

    file_put_contents("files/" . $edited_file, $new_content);

    echo "Modifications enregistrées avec succès!";
}

if (array_key_exists('delete_file', $_POST) && array_key_exists('file_to_delete', $_POST)) {
    $file_to_delete = $_POST['file_to_delete'];
    delete_file($file_to_delete);
}

if (array_key_exists('upload_file', $_POST)) {
    upload_file();
}

function create_file($file_name = "nouveau_fichier")
{
    $file_path = "files/" . $file_name . ".mywac";

    if (!file_exists($file_path)) {
        $myfile = fopen($file_path, "w");
        fclose($myfile);
        echo $file_name . " créé avec succès !";
    } else {
        echo "Le fichier existe déjà.";
    }
}

function display_files()
{
    if ($handle = opendir('./files')) {
        while (false !== ($entry = readdir($handle))) {
            if ($entry != "." && $entry != "..") {
                echo "<h2><p>$entry</p></h2>";
                echo "<form method=\"post\">
                        <input type=\"hidden\" name=\"editfile\" value=\"$entry\" />
                        <input type=\"submit\" class=\"button\" value=\"Modifier\" />
                        <input type=\"submit\" class=\"button\" name=\"delete_file\" value=\"Supprimer\" />
                        <input type=\"hidden\" name=\"file_to_delete\" value=\"$entry\" />
                        <a href='download.php?file=files/$entry&type=pdf'>Télécharger le fichier</a>
                    </form>";

                echo "<form method=\"post\">
                        <input type=\"hidden\" name=\"edited_file\" value=\"$entry\" />
                        <input type=\"submit\" name=\"export_html\" class=\"button\" value=\"Exporter en HTML\" />
                    </form>";

                echo "<form method=\"post\">
                        <input type=\"hidden\" name=\"edited_file\" value=\"$entry\" />
                        <input type=\"submit\" name=\"export_pdf\" class=\"button\" value=\"Exporter en PDF\" />
                    </form>";
            }
        }
        closedir($handle);
    }
}

if (array_key_exists('editfile', $_POST)) {
    $file_name = $_POST['editfile'];
    edit_files($file_name);
}

function edit_files($file_name)
{
    $file_path = "files/" . $file_name;

    $file_extension = pathinfo($file_path, PATHINFO_EXTENSION);

    if (file_exists($file_path)) {
        if (strtolower($file_extension) === 'mywac') {
            $content = file_get_contents($file_path);

            echo "<form method=\"post\" action=\"{$_SERVER['PHP_SELF']}\">
                <div style=\"display: flex;\">
                    <div>
                        <textarea name=\"file_content\" id=\"file_content\" rows=\"10\" cols=\"50\" oninput=\"updatePreview(this.value);\">$content</textarea>
                    </div>
                    <div id=\"preview\"></div>
                </div>
                <br>
                <button type=\"button\" onclick=\"applyFormatting('bold');\">Gras</button>
                <button type=\"button\" onclick=\"applyFormatting('italic');\">Italique</button>
                <button type=\"button\" onclick=\"applyFormatting('underline');\">Souligner</button>
                <button type=\"button\" onclick=\"applyFormatting('insertUnorderedList');\">Liste à puce</button>
                <button type=\"button\" onclick=\"applyFormatting('insertOrderedList');\">Liste numérotée</button>
                <button type=\"button\" onclick=\"applyFormatting('formatBlock', 'h2');\">Titre</button>
                <button type=\"button\" onclick=\"changeColor();\">Changer la couleur</button>
                <input type=\"hidden\" name=\"edited_file\" value=\"$file_name\" />
                <br>
                <input type=\"submit\" name=\"save_changes\" value=\"Enregistrer les modifications\" />
            </form>";

            echo "<script>
                function updatePreview(value) {
                    document.getElementById('preview').innerHTML = value;
                }
            </script>";
        } else {
            echo "La création et l’édition des fichiers en ligne ne sont possible que pour les fichiers de type .mywac";
        }
    } else {
        echo "Le fichier n'existe pas.";
    }
}


function delete_file($file_name)
{
    $file_path = "files/" . $file_name;

    if (file_exists($file_path)) {
        unlink($file_path);
        echo "Le fichier $file_name a été supprimé avec succès!";
    } else {
        echo "Le fichier $file_name n'existe pas.";
    }
}

function upload_file()
{
    if (isset($_FILES['uploaded_file'])) {
        $file_name = basename($_FILES['uploaded_file']['name']);
        $target_path = "files/" . $file_name;

        if (move_uploaded_file($_FILES['uploaded_file']['tmp_name'], $target_path)) {
            echo "Le fichier $file_name a été téléchargé avec succès!";
        } else {
            echo "Erreur lors du téléchargement du fichier.";
        }
    }
}
?>

<form method="post" enctype="multipart/form-data">
    <div>
        <input type="file" name="uploaded_file" />
        <input type="submit" name="upload_file" class="button" value="Uploader un fichier" />
    </div>
    <div>
        <input type="text" name="file_name" value="nouveau_fichier" />
        <input type="submit" name="create_file" class="button" value="Créer un fichier" />
    </div>
</form>
<script>
    function applyFormatting(command, value = null) {
        var textarea = document.getElementById('file_content');
        var start = textarea.selectionStart;
        var end = textarea.selectionEnd;
        var selectedText = textarea.value.substring(start, end);

        var prefix = "";
        var suffix = "";

        switch (command) {
            case 'bold':
                prefix = '<strong>';
                suffix = '</strong>';
                break;
            case 'italic':
                prefix = '<em>';
                suffix = '</em>';
                break;
            case 'underline':
                prefix = '<u>';
                suffix = '</u>';
                break;
            case 'insertUnorderedList':
                prefix = '<ul><li>';
                suffix = '</li></ul>';
                break;
            case 'insertOrderedList':
                prefix = '<ol><li>';
                suffix = '</li></ol>';
                break;
            case 'formatBlock':
                prefix = `<${value}>`;
                suffix = `</${value}>`;
                break;
            case 'color':
                prefix = `<span style="color: ${value}">`;
                suffix = '</span>';
                break;
            default:
                break;
        }

        var newText = textarea.value.substring(0, start) + prefix + selectedText + suffix + textarea.value.substring(end);
        textarea.value = newText;

        updatePreview(newText);
    }

    function changeColor() {
        var color = prompt("Entrez la couleur (nom ou code hexadécimal) :");
        if (color !== null) {
            applyFormatting('color', color);
        }
    }


    function updatePreview(value) {
        var previewElement = document.getElementById('preview');
        previewElement.innerHTML = value;
    }
</script>