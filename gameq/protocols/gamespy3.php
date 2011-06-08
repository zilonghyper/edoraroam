<?php
/**
 * This file is part of GameQ.
 *
 * GameQ is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * GameQ is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * GameSpy3 Protocol Class
 *
 * This class is used as the basis for all game servers
 * that use the GameSpy3 protocol for querying
 * server status.
 *
 * @author Austin Bischoff <austin@codebeard.com>
 */
abstract class GameQ_Protocols_Gamespy3 extends GameQ_Protocols
{
	/*
	 * Constants
	 */
	const PLAYERS = 1;
	const TEAMS = 2;

	/**
	 * Array of packets we want to look up.
	 * Each key should correspond to a defined method in this or a parent class
	 *
	 * @var array
	 */
	protected $packets = array(
		self::PACKET_CHALLENGE => "\xFE\xFD\x09\x10\x20\x30\x40\xFF\xFF\xFF\x01",
		self::PACKET_DETAILS => "\xFE\xFD\x00\x10\x20\x30\x40%s\xFF\xFF\xFF\x01",
	);

	/**
	 * Methods to be run when processing the response(s)
	 *
	 * @var array
	 */
	protected $process_methods = array(
		"process_details",
		"process_players",
	);

	/**
	 * Default port for this server type
	 *
	 * @var int
	 */
	protected $port = 1; // Default port, used if not set when instanced

	/**
	 * The protocol being used
	 *
	 * @var string
	 */
	protected $protocol = 'gamespy3';

	/**
	 * String name of this protocol class
	 *
	 * @var string
	 */
	protected $name = 'gamespy3';

	/**
	 * Longer string name of this protocol class
	 *
	 * @var string
	 */
	protected $name_long = "Gamespy3";

	/*
	 * Abstract Methods (required)
	 */

	/**
	 * Parse the challenge response and apply it to all the packet types
	 * that require it.
	 *
	 * @see GameQ_Protocols_Core::parseChallengeAndApply()
	 */
 	public function parseChallengeAndApply()
    {
    	//var_dump($this->challenge_buffer->getBuffer()); exit;

    	// Skip the header
    	$this->challenge_buffer->skip(5);


    	// Apply the challenge and return
    	return $this->challengeApply(pack("H*", sprintf("%08X", (int) $this->challenge_buffer->readString())));
    }

    /*
     * Internal methods
     */

	protected function process_details()
	{
		// Set the result to a new result instance
		$result = new GameQ_Result();

    	$packets = $this->packets_response[self::PACKET_DETAILS];

    	var_dump($packets);  exit;

		// Incomplete, unable to test
		return array();
	}

	protected function process_players()
	{
		// Incomplete, unable to test
		return array();
	}

	/**
	 * Parse the sub sections of the returned data, usually teams/players info
	 *
	 * @param int $type
	 * @param GameQ_Buffer $buf
	 * @param GameQ_Result $result
	 */
	protected function parseSub($type, GameQ_Buffer &$buf, GameQ_Result &$result)
	{
		// Get the proper string type
		switch($type)
		{
			case self::PLAYERS:
				$type_string = 'players';
				break;

			case self::TEAMS:
				$type_string = 'teams';
				break;
		}

		// Loop until we run out of data
		while ($buf->getLength())
		{
            // Get the header
            $header = $buf->readString();

            // No header so break
            if ($header == "")
            {
            	break;
            }

            // Rip off any trailing stuff
            $header = rtrim($header, '_');

            // Skip next position because it should be empty
            $buf->skip();

            // Get the values
            while ($buf->getLength())
            {
            	// Grab the value
                $val = $buf->readString();

                // No value so break
                if ($val === '')
                {
					break;
                }

                // Add the proper value
                $result->addSub($type_string, $header, trim($val));
            }
        }

        return TRUE;
	}
}