<?php

class FixturesModel extends JackalModel {
	private $_femaleName = array();
	private $_firstName  = array();
	private $_lastName = array();
	private $_maleName   = array();

	public function __construct() {
		$this->_femaleName = explode(" ", "Isabella Sophia Emma Olivia Ava Emily Abigail Madison Chloe Mia Addison Elizabeth Ella Natalie Samantha Alexis Lily Grace Hailey Alyssa Lillian Hannah Avery Leah Nevaeh Sofia Ashley Anna Brianna Sarah Zoe Victoria Gabriella Brooklyn Kaylee Taylor Layla Allison Evelyn Riley Amelia Khloe Makayla Aubrey Charlotte Savannah Zoey Bella Kayla Alexa Peyton Audrey Claire Arianna Julia Aaliyah Kylie Lauren Sophie Sydney Camila Jasmine Morgan Alexandra Jocelyn Gianna Maya Kimberly Mackenzie Katherine Destiny Brooke Trinity Faith Lucy Madelyn Madeline Bailey Payton Andrea Autumn Melanie Ariana Serenity Stella Maria Molly Caroline Genesis Kaitlyn Eva Jessica Angelina Valeria Gabrielle Naomi Mariah Natalia Paige Rachel Mya Rylee Katelyn Ellie Isabelle Vanessa Lilly London Mary Kennedy Lydia Jordyn Ruby Scarlett Jade Isabel Annabelle Sadie Harper Jennifer Sara Nicole Violet Liliana Michelle Stephanie Reagan Jada Adriana Gracie Megan Jayla Kendall Lyla Amy Reese Rebecca Laila Kylee Izabella Jenna Brooklynn Aliyah Piper Juliana Mckenzie Giselle Gabriela Valerie Daniela Daisy Valentina Makenzie Haley Lila Ashlyn Melissa Vivian Nora Angela Katie Hayden Elena Summer Eleanor Keira Clara Jillian Eliana Alana Jacqueline Alice Adrianna Alivia Miranda Julianna Aniyah Jordan Mikayla Eden Skylar Margaret Briana Ryleigh Shelby Josephine Delilah Amanda Allie Addyson Diana Brielle Catherine Angel Danielle Elise Leslie Melody Ana Penelope");
		$this->_firstName  = array_merge($this->_maleName, $this->_femaleName);
		$this->_lastName = explode(" ", "Smith Johnson Williams Jones Brown Davis Miller Wilson Moore Taylor Anderson Thomas Jackson White Harris Martin Thompson Garcia Martinez Robinson Clark Rodriguez Lewis Lee Walker Hall Allen Young Hernandez King Wright Lopez Hill Scott Green Adams Baker Gonzalez Nelson Carter Mitchell Perez Roberts Turner Phillips Campbell Parker Evans Edwards Collins Stewart Sanchez Morris Rogers Reed Cook Morgan Bell Murphy Bailey Rivera Cooper Richardson Cox Howard Ward Torres Peterson Gray Ramirez James Watson Brooks Kelly Sanders Price Bennett Wood Barnes Ross Henderson Coleman Jenkins Perry Powell Long Patterson Hughes Flores Washington Butler Simmons Foster Gonzales Bryant Alexander Russell Griffin Diaz Hayes Myers Ford Hamilton Graham Sullivan Wallace Woods Cole West Jordan Owens Reynolds Fisher Ellis Harrison Gibson Mcdonald Cruz Marshall Ortiz Gomez Murray Freeman Wells Webb Simpson Stevens Tucker Porter Hunter Hicks Crawford Henry Boyd Mason Morales Kennedy Warren Dixon Ramos Reyes Burns Gordon Shaw Holmes Rice Robertson Hunt Black Daniels Palmer Mills Nichols Grant Knight Ferguson Rose Stone Hawkins Dunn Perkins Hudson Spencer Gardner Stephens Payne Pierce Berry Matthews Arnold Wagner Willis Ray Watkins Olson Carroll Duncan Snyder Hart Cunningham Bradley Lane Andrews Ruiz Harper Fox Riley Armstrong Carpenter Weaver Greene Lawrence Elliott Chavez Sims Austin Peters Kelley Franklin");
		$this->_maleName   = explode(" ", "Jacob Ethan Michael Jayden William Alexander Noah Daniel Aiden Anthony Joshua Mason Christopher Andrew David Matthew Logan Elijah James Joseph Gabriel Benjamin Ryan Samuel Jackson John Nathan Jonathan Christian Liam Dylan Landon Caleb Tyler Lucas Evan Gavin Nicholas Isaac Brayden Luke Angel Brandon Jack Isaiah Jordan Owen Carter Connor Justin Jose Jeremiah Julian Robert Aaron Adrian Wyatt Kevin Hunter Cameron Zachary Thomas Charles Austin Eli Chase Henry Sebastian Jason Levi Xavier Ian Colton Dominic Juan Cooper Josiah Luis Ayden Carson Adam Nathaniel Brody Tristan Diego Parker Blake Oliver Cole Carlos Jaden Jesus Alex Aidan Eric Hayden Bryan Max Jaxon Brian Bentley Alejandro Sean Nolan Riley Kaden Kyle Micah Vincent Antonio Colin Bryce Miguel Giovanni Timothy Jake Kaleb Steven Caden Bryson Damian Grayson Kayden Jesse Brady Ashton Richard Victor Patrick Marcus Preston Joel Santiago Maxwell Ryder Edward Miles Hudson Asher Devin Elias Jeremy Ivan Jonah Easton Jace Oscar Collin Peyton Leonardo Cayden Gage Eduardo Emmanuel Grant Alan Conner Cody Wesley Kenneth Mark Nicolas Malachi George Seth Kaiden Trevor Jorge Derek Jude Braxton Jaxson Sawyer Jaiden Omar Tanner Travis Paul Camden Maddox Andres Cristian Rylan Josue Roman Bradley Axel Fernando Garrett Javier Damien Peter Leo Abraham Ricardo Francisco Lincoln Erick Drake Shane");
		
		parent::__construct();
	}

	/**
	 * Returns an array of random information
	 * 
	 * @example 
	 * <code type='php'>
	 * Jackal::model("Fixtures/random/:firstName as name,(20..30) as age,:dateTime(m D y) as birthdate", array(":LIMIT" => 5));
	 * </code>
	 * 
	 * @param string $URI[0]        | The format of the columns
	 * @param int    $URI[":LIMIT"] | The number of items to return
	 *
	 * @return array
	 *
	 */
	public function random($URI) {
		// Initialize the fields array
		$fields = array();
		// Initialize the limit
		$limit = 100;

		//  __________________________________________________
		// / Parse URI                                        \ 
		
		foreach($URI as $name=>$value) {
			switch(strtolower($name)) {
				// Currently the only option supported
				case "0":
					// Chew on the field list
					preg_match_all('/(\(.*?\)|.*?)(?:\s+as\s+(\w+))?(,|$)/i', $value, $matches);

					// Add each match to the field list
					foreach(array_filter($matches[1]) as $i=>$match) {
						($j = $matches[2][$i]) || ($j = count($fields));
						$fields[$j] = $match;
					}

					break;
				case ":limit": $limit = (int) $value; break;
			}
		}

		// \__________________________________________________/

		
		//  __________________________________________________
		// / Chew fields                                      \

		foreach($fields as $field) {
			if($field[0] == ":") {
				preg_match('/:(\w+)(\(.*?\))?/', $field, $matches);
				$function = ucfirst(@$matches[1]);
				$parameters = @$matches[2];

				if(method_exists($this, "get$function")) {
					$data = array_fill(0, $limit, $parameters);
					$results[] = array_map(array($this, "get$function"), $data);
				}

				else {
					$results[] = array_fill(0, $limit, $field);
				}
			}
			
			else $results[] = array_map(array($this, "getItem"), array_fill(0, $limit, $field)) ; 
		}

		// \__________________________________________________/


		//  __________________________________________________
		// / Transpose the results                            \
		
		// This procedure rotates the result array 90 degrees
		array_unshift($results, null);
		$results = call_user_func_array("array_map", $results);
		
		// \__________________________________________________/

		//  __________________________________________________
		// / Apply aliases                                    \
		
		foreach($results as $i=>$result) {
			$results[$i] = array_combine(array_keys($fields), (array) $result);
		}

		// \__________________________________________________/
		
		return $results;
	}

	public function getDateTime($format="") {
		if(!$format) $format = "m D y";
		return date($format);
	}

	public function getFirstName() {
		return $this->_firstName[array_rand($this->_firstName)];
	}

	public function getItem($source) {
		$source = preg_replace('/\((.*)(\d+)\.\.(\d+)\)/e', '"($1".implode(",$1", range($2, $3)).")"', $source);
		$data = explode(",", trim($source, "()"));
		return $data[array_rand($data)];
	}

	public function getLastName() {
		return $this->_lastName[array_rand($this->_lastName)];
	}
}

