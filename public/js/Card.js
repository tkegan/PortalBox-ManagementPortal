import { SessionTimeOutError } from './SessionTimeOutError.js';

/**
 * Hide away the details of working with the REST API
 */
export class Card {
	/**
	 * Get a list of cards
	 *
	 * @return Array<Card> a list of cards
	 * @throws SessionTimeOutError if the user session has expired
	 * @throws String if any other error occurs
	 */
	static async list(query = '') {
		const response = await fetch("/api/cards.php?" + query, { "credentials": "same-origin" });

		if(response.ok) {
			return await response.json();
		}

		if(403 == response.status) {
			throw new SessionTimeOutError();
		}

		throw "API was unable to list cards";
	}

	/**
	 * Get a card by id
	 *
	 * @param int id the unique id of the Card to retrieve
	 * @return Card specified by the id
	 * @throws SessionTimeOutError if the user session has expired
	 * @throws String if any other error occurs
	 */
	static async read(id) {
		const response = await fetch("/api/cards.php?id=" + id, { "credentials": "same-origin" });

		if(response.ok) {
			return await response.json();
		}

		if(403 == response.status) {
			throw new SessionTimeOutError();
		}

		throw "API was unable to find card: " + id;
	}

	/**
	 * Add card to those tracked by API
	 *
	 * @return Card as tracked by API
	 * @throws SessionTimeOutError if the user session has expired
	 * @throws String if any other error occurs
	 */
	static async create(data) {
		const response = await fetch("/api/cards.php", {
			body: JSON.stringify(data),
			credentials: "include",
			headers: {
				"Content-Type": "application/json"
			},
			method: "PUT"
		});

		if(response.ok) {
			return await response.json();
		}

		if(403 == response.status) {
			throw new SessionTimeOutError();
		}

		throw "API was unable to save new card";
	}
}
