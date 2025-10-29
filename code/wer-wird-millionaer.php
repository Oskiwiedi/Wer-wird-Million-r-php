<?php

/*
Author: Oskar Wiederhold
Date: 29.10.2025
*/

$daten = json_decode(file_get_contents("fragen.json"), true);
$alleFragen = $daten["fragen"];

// Group questions by "stufe"
$fragenNachStufe = [];
foreach ($alleFragen as $frage) {
    $fragenNachStufe[$frage["stufe"]][] = $frage;
}

// Order of the money stages
$stufen = [
    "50 CHF", "100 CHF", "200 CHF", "300 CHF", "500 CHF",
    "1.000 CHF", "2.000 CHF", "4.000 CHF", "8.000 CHF", "16.000 CHF",
    "32.000 CHF", "64.000 CHF", "125.000 CHF", "500.000 CHF", "1.000.000 CHF"
];

// Build the question list: randomly one per stage
$fragen = [];
foreach ($stufen as $stufe) {
    if (isset($fragenNachStufe[$stufe])) {
        $zufallsFrage = $fragenNachStufe[$stufe][array_rand($fragenNachStufe[$stufe])];
        $fragen[] = $zufallsFrage;
    }
}

// Wait for Enter
function warteAufEnter(string $nachricht = "Drücke Enter, um fortzufahren..."): void
{
    do {
        $eingabe = readline($nachricht . "\n");
    } while ($eingabe !== "");
}

// Intro
echo "============ Willkommen bei Wer wird Millionär! ============\n";
echo "Ich bin Günther Jauch und werde Sie durch die Show begleiten.\n\n";

$name = readline("Wie heisst du: ");
$home = readline("Wo wohnst du: ");
$work = readline("Was willst du mit dem Geld machen: ");

echo "\nNa dann viel Glück, $name aus $home! Hoffentlich kannst du deinen Traum verwirklichen.\n";

warteAufEnter("Dann fangen wir mal an mit der ersten Frage (Enter):");

// Current winnings
$gewinn = "0 CHF";
$sicherheitsstufe = "0 CHF";

// Safety levels
$sicherheitsfragen = ["500 CHF", "16.000 CHF"];

// Jokers
$joker_5050 = true;
$joker_audience = true;

// Game loop
foreach ($fragen as $frage) {
    echo "\nFrage für {$frage['stufe']}:\n";
    echo $frage["frage"] . "\n";

    $antworten = $frage["antworten"];

    while (true) {
        // Show current answers
        foreach ($antworten as $buchstabe => $text) {
            echo "$buchstabe) $text\n";
        }

        // Hinweis auf Joker und Aufhören
        echo "\n(Tipp: '5' für 50/50, 'P' für Publikumjoker, 'Q' um aufzuhören)\n";

        $eingabe = strtoupper(trim(readline("Deine Antwort: ")));

        // --- Spieler möchte aufhören ---
        if ($eingabe === "Q") {
            echo "\nDu hast das Spiel freiwillig beendet.\n";
            echo "Du nimmst $gewinn mit nach Hause.\n";
            break 2; // beendet beide Schleifen
        }

        // --- 50/50 Joker ---
        if ($eingabe === "5" && $joker_5050) {
            echo "\n[50/50 Joker aktiviert]\n";
            $richtige = $frage["richtigeAntwort"];
            $wrong = array_diff(array_keys($antworten), [$richtige]);
            shuffle($wrong);
            $remove = array_slice($wrong, 0, 2);
            foreach ($remove as $key) {
                unset($antworten[$key]);
            }
            $joker_5050 = false;
            continue;
        }

        // --- Publikumjoker ---
        if ($eingabe === "P" && $joker_audience) {
            echo "\n[Publikumjoker aktiviert]\n";
            $alleKeys = array_keys($antworten);
            $empfehlung = $alleKeys[array_rand($alleKeys)];
            echo "Das Publikum tippt auf Antwort: $empfehlung\n";
            $joker_audience = false;
            continue;
        }

        // --- Check answer ---
        if (isset($antworten[$eingabe])) {
            if ($eingabe === strtoupper($frage["richtigeAntwort"])) {
                echo "Richtig! Du hast {$frage['stufe']} gewonnen!\n";
                $gewinn = $frage["stufe"];

                if (in_array($frage["stufe"], $sicherheitsfragen, true)) {
                    $sicherheitsstufe = $frage["stufe"];
                    echo "Das ist eine Sicherheitsstufe! Du behältst mindestens $sicherheitsstufe.\n";
                }

                if ($frage["stufe"] === "1.000.000 CHF") {
                    echo "\nUnglaublich! Du bist Millionär!\n";
                }

                break;
            } else {
                echo "Falsch! Richtige Antwort war: {$frage['richtigeAntwort']}\n";
                echo "Du gehst mit $sicherheitsstufe nach Hause.\n";
                $gewinn = $sicherheitsstufe;
                break 2;
            }
        } else {
            echo "Ungültige Eingabe. Versuche es erneut.\n";
        }
    }
}

// End of the game
echo "\n==============================\n";
echo "Danke fürs Mitspielen!\n";
echo "Endgültiger Gewinn: $gewinn\n";
echo "==============================\n";
