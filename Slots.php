<?php

function parseDimensions($input)
{
    $dimensions = explode('x', $input);

    if (count($dimensions) != 2) {
        return false;
    }

    $row = intval(trim($dimensions[0]));
    $column = intval(trim($dimensions[1]));

    return array($row, $column);
}

function displayBoard($board)
{
    foreach ($board as $row) {
        echo implode(" ", $row) . PHP_EOL;
    }
}

function generateBoard($rows, $cols, $elements)
{
    $board = [];

    for ($i = 0; $i < $rows; $i++) {
        $row = [];
        for ($j = 0; $j < $cols; $j++) {
            $randomIndex = array_rand($elements);
            $row[] = $elements[$randomIndex];
        }
        $board[] = $row;
    }

    return $board;
}

function calculateWin($board, $winCondition)
{
    $win = false;
    $winningSymbol = null;

    switch ($winCondition) {
        case 'rowcolumn':
            // Check rows
            foreach ($board as $row) {
                if (count(array_unique($row)) === 1) {
                    $win = true;
                    $winningSymbol = $row[0];
                    break;
                }
            }


            if (!$win) {
                for ($i = 0; $i < count($board[0]); $i++) {
                    $column = array_column($board, $i);
                    if (count(array_unique($column)) === 1) {
                        $win = true;
                        $winningSymbol = $column[0];
                        break;
                    }
                }
            }
            break;

        case 'diagonals':
            $diag1 = array_map(function ($i) use ($board) {
                return $board[$i][$i];
            }, array_keys($board));

            $diag2 = array_map(function ($i) use ($board) {
                $j = count($board) - $i - 1;
                return $board[$i][$j];
            }, array_keys($board));

            if (count(array_unique($diag1)) === 1) {
                $win = true;
                $winningSymbol = $diag1[0];
            } elseif (count(array_unique($diag2)) === 1) {
                $win = true;
                $winningSymbol = $diag2[0];
            }
            break;

        case 'anyrow':
            foreach ($board as $row) {
                $uniqueSymbols = array_unique($row);
                if (count($uniqueSymbols) === 1 && $uniqueSymbols[0] !== ' ') {
                    $win = true;
                    $winningSymbol = $uniqueSymbols[0];
                    break;
                }
            }
            break;

        default:
            break;
    }

    return array('win' => $win, 'symbol' => $winningSymbol);
}

function getMultipliers($element)
{
    $multipliers = [
        "*" => 4,
        "@" => 4,
        "X" => 2,
        "$" => 10,
        "7" => 100
    ];
    return $multipliers[$element];
}

//$elements = ["*", "*", "*", "7", "$", "7", "7", "7", "@", "*", "*", "*", "X"];
$elements = ["*", "*", "*", "$", "$", "7", "@", "@", "@", "X", "X", "X", "X"];
$baseBet = 5;

$coins = (int)readline("Enter the amount of coins you'd like to start with: ");
if (empty($coins) === true || $coins < 0) {
    echo "Input a valid amount." . PHP_EOL;
    exit;
}
$boardSize = readline("Enter the size of the board you'd like (ex. 3x3): ");
$dimensions = parseDimensions($boardSize);

if ($dimensions === false) {
    echo "Invalid input format. Please enter dimensions in the format 'NxM'.";
    exit;
} else {
    list($rows, $cols) = $dimensions;

    $winCondition = 0;

    while (true) {
        $input = ucfirst(strtolower(readline("Please input your desired action [Playgame, Bet, Board, Selectwin, Exit]: ")));

        switch ($input) {
            case "Playgame":
                if ($winCondition === false) {
                    echo "Please select a win condition before playing the game." . PHP_EOL;
                    break;
                }

                $totalCoinsWon = 0;

                do {
                    $board = generateBoard($rows, $cols, $elements);
                    displayBoard($board);

                    $winResult = calculateWin($board, $winCondition);
                    if ($winResult['win']) {
                        echo "Congratulations! You win!" . PHP_EOL;
                        $multiplier = getMultipliers($winResult['symbol']);
                        $wonCoins = $baseBet * $multiplier;
                        echo "You won $wonCoins coins!" . PHP_EOL;
                        $totalCoinsWon += $wonCoins;
                    } else {
                        echo "Sorry, you lose!" . PHP_EOL;
                        $coins -= $baseBet;
                    }

                    echo "Coins Left: $coins" . PHP_EOL;
                    echo PHP_EOL;
                    $continue = readline("Do you want to play again? (Y/N): ");
                } while (strtoupper($continue) === 'Y');

                $coins += $totalCoinsWon;
                break;

            case "Bet":
                $baseBet = (int)readline("Please select your bet amount: ");
                break;

            case "Board":
                $newBoardSize = readline("Enter the new size of the board (ex. 3x3): ");
                $newDimensions = parseDimensions($newBoardSize);
                if ($newDimensions === false) {
                    echo "Invalid input format. Please enter dimensions in the format 'NxM'." . PHP_EOL;
                } else {
                    list($rows, $cols) = $newDimensions;
                }
                break;

            case "Selectwin":
                $winOptions = ['rowcolumn', 'diagonals', 'anyrow'];
                $winCondition = strtolower(readline("Please select the win condition [Row and Column, Diagonals, Any Row]: "));
                if (in_array($winCondition, $winOptions)) {
                    echo "Win condition selected: $winCondition" . PHP_EOL;
                } else {
                    echo "Invalid win condition. Please try again." . PHP_EOL;
                }
                break;

            case "Exit":
                exit("Goodbye!");

            default:
                echo "Invalid input. Please try again." . PHP_EOL;
                break;
        }
    }
}