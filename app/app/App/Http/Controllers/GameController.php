<?php

namespace App\Http\Controllers;

use Components\Enums\GameMark;
use Components\Enums\GamePlayer;
use Components\GameBoard\GameBoard;
use Exception;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;

class GameController extends Controller
{

    /**
     * @param GameBoard $game
     * @return Response
     * @throws Exception
     */
    protected function status_output( GameBoard $game ): Response {

        // Generate a status text for the end of the game.
        $winner = $this->whoHasWon( $game );
        if ( $this->someoneHasWon( $game ) && !$winner )
            $final = "\nSomeone has won the game!";
        elseif ( $this->someoneHasWon( $game ) && $winner === GamePlayer::Human)
            $final = "\nYou have won the game! Congratulations!";
        elseif ( $this->someoneHasWon( $game ) && $winner === GamePlayer::Robot)
            $final = "\nThe bot has won the game...";
        elseif ( !$game->spaceIsLeft() )
            $final = "\nIt's a draw!";
        else $final = '';

        return response(CopyrightController::getCopyright() . "\n\n{$game->draw()}{$final}")->header('Content-Type', 'text/plain');
    }

    /**
     * @param GameBoard $game
     * @return bool
     * @throws Exception
     */
    protected function someoneHasWon( GameBoard $game ): bool {
        // ##### TASK 8 - Make this check more efficient ###############################################################
        // =============================================================================================================
        // This function checks if the game has already won. It does this by checking for every possible winning
        // condition. For example, the first block below checks if the first row contains identical marks that are not
        // GameMark::None.
        // As you can see, this function is exorbitantly long and highly redundant. Your task is to find a way to
        // shorten this function without compromising its functionality. Note that by "shorten", we don't mean to just
        // remove spaces and line breaks ;)
        // =============================================================================================================
        //testLines returning true if wincon reached in specified area
        if ($this->testLines($game, "getRow", 4)||
            $this->testLines($game, "getColumn", 4)||
            $this->testLines($game, "getMainDiagonal", 1)||
            $this->testLines($game, "getAntiDiagonal", 1))
            return true;
        //no wincon reached
        return false;
    }
    
    /**
     * @param GameBoard $game
     * @return bool
     * @throws Exception
     */
    protected function testLines( Gameboard $game, $lineToTest, $y ): bool {
        //testLines($game, directionOfLinesToTest, amountOfLinesToTest)
        //will return true if a wincon in the requested area is reached
        for ($x = 0; $x <= $y-1; $x++) {
            if (    // Check lines
                $game->$lineToTest($x)->getSpace( 0 ) === $game->$lineToTest($x)->getSpace( 1 ) &&
                $game->$lineToTest($x)->getSpace( 0 ) === $game->$lineToTest($x)->getSpace( 2 ) &&
                $game->$lineToTest($x)->getSpace( 0 ) === $game->$lineToTest($x)->getSpace( 3 ) &&
                $game->$lineToTest($x)->getSpace( 0 ) !== GameMark::None
            ) return true;
        }
        return false;
    }    

    protected function whoHasWon( GameBoard $game ): ?GamePlayer {
        // ##### TASK 7 - Check who has won ############################################################################
        // =============================================================================================================
        // Here, you need to code a way to find out who has won the game.
        // This function needs to return null if nobody has won yet - you can use someoneHasWon( $game ) for this.
        // If someone has won, it needs to return either GamePlayer::Human or GamePlayer::Robot.
        // =============================================================================================================
        //check if someone won
        $Gamestate = $this->someoneHasWon( $game );
        if ($Gamestate) {
            //last player to have a turn won
            $lastPlayer = $game->getLastPlayer();
            return $lastPlayer;
        }
        //nobody won yet
        return null;
    }

    /**
     * Is the given player allowed to take the next turn?
     * @param GameBoard $game
     * @param GamePlayer $player
     * @return bool
     */
    protected function isAllowedToPlay( GameBoard $game, GamePlayer $player) : bool {

        // ##### TASK 6 - No cheating! #################################################################################
        // =============================================================================================================
        // We don't want the player to be able to cheat. They should only be able to make a move if it is their turn.
        // Neither the player nor the bot are allowed to make a move twice in a row. So, you need to check which player
        // made the *last* move to find out if the player is allowed to act.
        // =============================================================================================================

        // The method $game->getLastPlayer() will return either GamePlayer::Robot (the last move was made by the bot),
        // GamePlayer::Human (the last move was made by the player) or GamePlayer::None (this is the first move).
        // Inside of $player you have the player which wants to play now.
        // If he is allowed to play, you have to return true, otherwise you have to return false.

        //check if last player is this player
        $lastPlayer = $game->getLastPlayer();
        if ($player != $lastPlayer) {
            //player is allowed to play the turn
            return true;
        }
        //player attempting to take double turn
        return false;
    }

    /**
     * @param int $x The x position entered by the player
     * @param int $y The y position entered by the player
     * @return Response
     * @throws Exception
     */
    public function play(int $x, int $y): Response
    {
        // Loading the game board
        $game = GameBoard::load();

        // Check if the given position is actually valid; can't have the player draw a cross on the table next to the
        // game board ;)
        if ($x < 0 || $x > 3 || $y < 0 || $y > 3)
            return response("Position outside of the game board")->setStatusCode(422)->header('Content-Type', 'text/plain');

        // Prevent the player from playing if the game has already ended
        if ($this->someoneHasWon( $game ) || !$game->spaceIsLeft())
            return response("You are not allowed to play. The game has already ended.")->setStatusCode(403)->header('Content-Type', 'text/plain');

        // Prevent the player from playing if it is not his turn
        if (!$this->isAllowedToPlay($game, GamePlayer::Human))
            return response("You are not allowed to play. It is the bots turn!")->setStatusCode(403)->header('Content-Type', 'text/plain');

        // ##### TASK 4 - Let the player make their move ###############################################################
        // =============================================================================================================
        // Here, you need to code the logic that allows a player to make a move.
        // You can make use of the methods offered by the $game object.
        // =============================================================================================================

        // We've previously ensured that the player is allowed to play and the game has not ended yet.
        // The method $game->getSpace( $x, $y ) will return the content of a space - either GameMark::None (free),
        // GameMark::Cross (belongs to the bot) or GameMark::Circle (belongs to the player).
        // You can compare two values with
        // $a === $b       gets true if $a is equals $b
        // $a !== $b       gets true if $a is not equals $b
        //
        // Once all the checks have passed, you can finally update the game board by calling
        // $game->setSpace( $x, $y, GameMark::Circle ).

        //check if coordinates !empty
        $GameMark = $game->getSpace($x, $y);
        if($GameMark !== GameMark::None) {
            /*Error: 403 position already occupied*/
            return response("This space has already been claimed!")->setStatusCode(403)->header('Content-Type', 'text/plain');
        }

        // If the space is not free, run the code in the line below by removing the //
        //return response("This space has already been claimed!")->setStatusCode(403)->header('Content-Type', 'text/plain');

        $game->setSpace( $x, $y, GameMark::Circle );

        // Saving the game board and output it to the player
        $game->save();
        return $this->status_output( $game );
    }

    /**
     * The MÜNSMEDIA GmbH bot plays one turn
     * @return Response
     * @throws Exception
     */
    public function playBot(): Response
    {
        // Load the current game board
        $game = GameBoard::load();

        // ##### TASK 5 - Understand the bot ###########################################################################
        // =============================================================================================================
        // This first step to beat your enemy is to thoroughly understand them.
        // Luckily, as a developer, you can literally look into its head. So, check out the bot logic and try to
        // understand what it does.
        // =============================================================================================================

        // Prevent the bot from playing if the game has already ended
        if ($this->someoneHasWon( $game ) || !$game->spaceIsLeft())
            return response("Bot is not allowed to play. The game has already ended.")->setStatusCode(403)->header('Content-Type', 'text/plain');

        // is the bot really allowed to play?
        if (!$this->isAllowedToPlay($game, GamePlayer::Robot))
            return response("Bot is not allowed to play. It is your turn!")->setStatusCode(403)->header('Content-Type', 'text/plain');

        $freeSpaces = [];

        // get all rows of our game board
        foreach ($game->getRows() as $y => $row) {
            // get all spaces inside the row
            foreach ($row->getSpaces() as $x => $space) {
                // check whether the space is still free
                if ($space->free()) {
                    // save the free space to our free spaces array
                    $freeSpaces[] = ['x' => $x, 'y' => $y];
                }
            }
        }

        // get random free space from our array - https://laravel.com/docs/9.x/helpers#method-array-random
        $randomFreeSpaceXY = Arr::random($freeSpaces);

        // mark field with a cross
        $game->setSpace($randomFreeSpaceXY['x'], $randomFreeSpaceXY['y'], GameMark::Cross);

        // save changed game board
        $game->save();

        return $this->status_output($game);
    }

    /**
     * Displays the board
     * @return Response
     */
    public function display(): Response
    {
        // Load the current game  and displays it
        return $this->status_output( GameBoard::load() );
    }

    /**
     * Resets the board
     * @return Response
     */
    public function reset(): Response
    {
        // Load the current game board
        $game = GameBoard::load();
        $game->clear();
        $game->save();

        return $this->status_output( $game );
    }
}
