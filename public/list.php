<?php

declare(strict_types=1);

require __DIR__ . '/../bootstrap.php';

use App\Testing\TestRegistry;

$tests = TestRegistry::all();
?>

<!DOCTYPE html>

<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link
		rel="stylesheet"
		href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,400,0,0"
	/>

<title>API Tester</title>

<style>
    * {
        box-sizing: border-box;
    }

    body {
        margin: 0;
        font-family: Arial, sans-serif;
        background: #f4f7fb;
        color: #1f2937;
    }

    main {
        max-width: 700px;
        margin: 40px auto;
        padding: 0 10px;
    }

    #content a {
        text-decoration: none;
        transition: color 250ms;
    }

    #content a:hover {
        color: #66f;
    }

    h1 {
        margin-bottom: 8px;
        font-size: 32px;
    }

    nav {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
        margin-bottom: 5px;
    }

    nav a {
        text-decoration: none;
        padding: 10px;
        border-radius: 10px;
        border: 1px solid rgba(0,0,0,0);
        background-color: #0ff;
        transition: border-color 250ms;
    }

    nav a.current {
        background-color: #0fa;
    }

    nav a:hover {
        border-color: #000;
    }

    .intro {
        margin-bottom: 20px;
        color: #6b7280;
    }

    .test {
		background: white;
		border: 1px solid #e5e7eb;
		border-radius: 12px;
		padding: 20px;
		margin-bottom: 12px;
		box-shadow: 0 3px 12px rgba(0, 0, 0, 0.04);
		cursor: pointer;
	}
	.test-header {
		display: flex;
		align-items: center;
		justify-content: space-between;
	}

	.test-name {
		color: #2563eb;
		font-size: 18px;
		font-weight: bold;
	}

	.test-arrow {
		font-family: 'Material Symbols Outlined';
		font-size: 24px;
		transition: transform 300ms ease;
	}

	.test.open .test-arrow {
		transform: rotate(180deg);
	}
    .test-content {
        height: 0;
        overflow: hidden;
        transition: height 300ms ease;
    }

    .test-name {
        color: #2563eb;
        font-size: 18px;
        font-weight: bold;
        margin-bottom: 6px;
    }

    .test-description {
        color: #6b7280;
        line-height: 1.5;
    }

    .test-demands {
        color: #6b7280;
    }

    .test-demands p {
        color: #222;
    }

    .test-demands p,
    .test-demands ul {
        margin: 0;
    }

    #content > div {
        display: none;
    }

    .codeTable {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
    }

    .codeTable > div:nth-of-type(2n) {
        margin-left: 20px;
        color: #888;
        user-select: none;
    }
</style>

</head>

<body>
<main>
    <h1>API Tester</h1>

<div>
    <p>
        Denna sidan är till för att med enkla tester testa ditt egna API!
        Det finns ett fåtal tester i nuläget, men jag ska försöka lägga till fler.
    </p>
</div>

<nav>
    <a href="#" onclick="openPage('files');" id="link_files">Kom igång</a>
    <a href="#" onclick="openPage('testlist');" id="link_testlist">Tester</a>
</nav>

<div id="content">

    <div id="files">
        <div class="intro">
            Du behöver tre saker för att kunna köra testerna.
            <ol>
                <li>
                    <a href="https://ngrok.com/download" target="_blank">
                        Installera "Ngrok".
                    </a>
                </li>
                <li>
                    <a href="files/TestRunner.java" target="_blank">
                        Ladda ner
                    </a>
                    och
                    <a href="#" onclick="openPage('javachange');">
                        ändra och flytta
                    </a>
                    TestRunner.java.
                </li>
                <li>
                    <a href="#" onclick="openPage('properties');">
                        Lägga till några rader i application.properties i din Java app
                    </a>.
                </li>
            </ol>
        </div>
    </div>

    <div id="testlist">
        <div class="intro">
            Tillgängliga tester för API:er. Titeln i blått är namnet som ska
            klistras in som värde till "test.name" i application.properties.
        </div>

        <?php foreach ($tests as $name => $test): ?>

            <div class="test">
				<div class="test-header">
					<div class="test-name">
						<?= htmlspecialchars($name) ?>
					</div>
					<span class="test-arrow">expand_more</span>
				</div>

                <div class="test-content">
                    <div class="test-description">
                        <?= htmlspecialchars($test["description"]) ?>
                    </div>

                    <div class="test-demands">
                        <p>Krav</p>
                        <ul>
                            <?php foreach ($test["demands"] as $demand): ?>
                                <li><?= htmlspecialchars($demand) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>

        <?php endforeach; ?>
    </div>

    <div id="javachange">
        <div class="intro">
            Två saker behöver du göra med TestRunner.Java efter att du
            <a href="files/TestRunner.java" target="_blank">Laddat ner den</a>.
        </div>

        <ol>
            <li>
                Överst i filen måste du ändra <b>package</b> till just det
                package som ditt projekt heter. Du kan kopiera första raden
                i din main-application klass och använda den.
            </li>
            <li>
                Du måste flytta filen så den ligger bredvid din
                main-application klass i ditt projekt. Det bör vara i mappen:
                <b>ProjektNamn -> src -> main -> java -> ditt.projekt.namn</b>
            </li>
        </ol>

        <p>
            Glöm inte att
            <a href="#" onclick="openPage('properties');">
                fixa application.properties
            </a>
            med!
        </p>
    </div>

    <div id="properties">
        <div class="intro">
            Fem rader behöver du lägga till i
            <b>application.properties</b>
            (som ligger i
            <i>ProjektNamn -> src -> main -> resources</i>).
        </div>

        <div class="codeTable">
            <div>
                <code>test.student_id=uniktKortId</code>
            </div>
            <div>
                - Ett unikt id för dig. Du väljer det själv, men se till att
                göra det unikt. T ex <i>PerLjung2026_a</i>. Detta ID sparas
                INTE utan används bara för att skapa en unik anslutning till testet.
            </div>

            <div>
                <code>test.name=TestetsNamn</code>
            </div>
            <div>
                - Det test du vill testa. Välj ett
                <a href="#" onclick="openPage('testlist');">
                    test från listan
                </a>.
            </div>

            <div>
                <code>test.username=AnvändarnamnTillInloggningMedJWT</code>
            </div>
            <div>
                - Ifall testet kräver inloggning så skickar du med ett testkonto här.
            </div>

            <div>
                <code>test.password=LösenordTillInloggningMedJWT</code>
            </div>
            <div>
                - Samma sak som förra raden, men såklart lösenord.
            </div>

            <div>
                <code>test.port=8080</code>
            </div>
            <div>
                - Eventuellt annan port ifall man har ändrat det.
            </div>
        </div>
    </div>

</div>

</main>

<script>
const openPage = page => {
    const childs = document.querySelector("#content").children;

    for (let child of childs) {
        child.style.display = "none";
    }

    document.querySelector("#" + page).style.display = "block";

    for (let child of document.querySelectorAll("nav > a")) {
        child.classList.remove("current");
    }

    if (document.querySelector("#link_" + page)) {
        document.querySelector("#link_" + page).classList.add("current");
    }
};

document.querySelector("#testlist").addEventListener("click", event => {
    const test = event.target.closest(".test");

    if (!test) {
        return;
    }

    const content = test.querySelector(".test-content");

    if (content.style.height === "0px" || !content.style.height) {
        content.style.height = content.scrollHeight + "px";
    } else {
        content.style.height = "0px";
    }
});

window.addEventListener("load", () => {
    openPage("files");
});

document.querySelector("#testlist").addEventListener("click", event => {
    const test = event.target.closest(".test");

    if (!test) {
        return;
    }

    const content = test.querySelector(".test-content");

    test.classList.toggle("open");

    if (test.classList.contains("open")) {
        content.style.height = content.scrollHeight + "px";
    } else {
        content.style.height = "0px";
    }
});
</script>

</body>
</html>
