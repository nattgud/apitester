package ditt.paket.namn; // Samma första tre ord som alla andra klasser. något.något.något

import org.springframework.boot.ApplicationArguments;
import org.springframework.boot.ApplicationRunner;
import org.springframework.stereotype.Component;

import java.util.regex.Matcher;
import java.util.regex.Pattern;

import java.net.URI;
import java.net.http.HttpClient;
import java.net.http.HttpRequest;
import java.net.http.HttpResponse;
import java.time.Duration;

import org.springframework.beans.factory.annotation.Value;

@Component
public class TestRunner implements ApplicationRunner {
	private static final String BASE_URL =
			"public_https_url_to_public_folder";

	private static final String TEST_SERVER =
			BASE_URL + "/index.php";

	private static final String REGISTER_URL =
			BASE_URL + "/register.php";

	private static final String API_KEY =
			"samekeyasinconfig";

	@Value("${test.student_id}")
	private String studentId;

	@Value("${test.name}")
	private String testName;

	@Value("${test.username}")
	private String testUsername;

	@Value("${test.password}")
	private String testPassword;

	@Value("${test.port}")
	private int testPort;

	@Override
	public void run(ApplicationArguments args) throws Exception {

		HttpClient client = HttpClient.newBuilder()
				.connectTimeout(Duration.ofSeconds(5))
				.build();

		Process ngrok = null;

		try {

			System.out.println(
					"Hämtar ngrok-token..."
			);

			String registerJson = """
                    {
                        "apiKey": "%s",
                        "studentId": "%s"
                    }
                    """.formatted(
					API_KEY,
					studentId
			);

			HttpRequest registerRequest =
					HttpRequest.newBuilder()
							.uri(URI.create(REGISTER_URL))
							.timeout(Duration.ofSeconds(15))
							.header(
									"Content-Type",
									"application/json"
							)
							.POST(
									HttpRequest.BodyPublishers
											.ofString(registerJson)
							)
							.build();

			HttpResponse<String> registerResponse =
					client.send(
							registerRequest,
							HttpResponse.BodyHandlers.ofString()
					);

			if (registerResponse.statusCode() < 200 ||
					registerResponse.statusCode() >= 300) {

				throw new RuntimeException(
						"Registrering misslyckades: HTTP " +
								registerResponse.statusCode() +
								"\n" +
								registerResponse.body()
				);
			}

			String registerBody =
					registerResponse.body();

			String ngrokToken =
					extractJsonValue(
							registerBody,
							"token"
					);

			if (ngrokToken == null ||
					ngrokToken.isBlank()) {

				throw new RuntimeException(
						"Servern returnerade inget ngrok-token."
				);
			}

			System.out.println(
					"ngrok-token mottaget."
			);

			System.out.println(
					"Startar ngrok..."
			);

			ngrok = new ProcessBuilder(
					"ngrok",
					"http",
					String.valueOf(testPort),
					"--authtoken",
					ngrokToken
			)
					.redirectErrorStream(true)
					.inheritIO()
					.start();

			String baseUrl = null;

			for (int i = 0; i < 30; i++) {

				try {

					Thread.sleep(500);

					HttpRequest tunnelsRequest =
							HttpRequest.newBuilder()
									.uri(URI.create(
											"http://127.0.0.1:4040/api/tunnels"
									))
									.timeout(
											Duration.ofSeconds(1)
									)
									.GET()
									.build();

					HttpResponse<String> response =
							client.send(
									tunnelsRequest,
									HttpResponse.BodyHandlers
											.ofString()
							);

					String json =
							response.body();
					if (json.contains(
							"\"public_url\":\"https://"
					)) {

						baseUrl =
								extractHttpsPublicUrl(json);

						if (baseUrl != null) {
							break;
						}
					}

				} catch (Exception ignored) {
					// ngrok not ready
				}
			}

			if (baseUrl == null) {
				throw new RuntimeException(
						"Kunde inte hitta ngrok-URL."
				);
			}

			System.out.println(
					"API: " + baseUrl
			);

			System.out.println(
					"Kör tester..."
			);

			String testJson = """
                    {
                        "apiKey": "%s",
                        "test": "%s",
                        "url": "%s",
                        "account": {
                            "username": "%s",
                            "password": "%s"
                        }
                    }
                    """.formatted(
					API_KEY,
					testName,
					baseUrl,
					testUsername,
					testPassword
			);

			HttpRequest testRequest =
					HttpRequest.newBuilder()
							.uri(URI.create(TEST_SERVER))
							.timeout(
									Duration.ofSeconds(60)
							)
							.header(
									"Content-Type",
									"application/json"
							)
							.POST(
									HttpRequest.BodyPublishers
											.ofString(testJson)
							)
							.build();

			HttpResponse<String> result =
					client.send(
							testRequest,
							HttpResponse.BodyHandlers
									.ofString()
					);


			printTestResult(result.body());

		} finally {

			if (ngrok != null &&
					ngrok.isAlive()) {

				System.out.println(
						"Stänger ngrok..."
				);

				ngrok.destroy();
			}
		}
	}

	private static void printTestResult(String json) {

		final String RESET = "\u001B[0m";
		final String GREEN = "\u001B[32m";
		final String RED = "\u001B[31m";
		final String CYAN = "\u001B[36m";
		final String BOLD = "\u001B[1m";

		System.out.println();
		System.out.println(
				CYAN + "========================================" + RESET
		);
		System.out.println(
				CYAN + BOLD + "           TESTRESULTAT" + RESET
		);
		System.out.println(
				CYAN + "========================================" + RESET
		);
		System.out.println();

		int total = extractInt(json, "total");
		int passed = extractInt(json, "passed");
		int failed = extractInt(json, "failed");

		System.out.println(
				BOLD +
						"Resultat: " +
						passed +
						" av " +
						total +
						" godkända" +
						RESET
		);

		System.out.println();

		Pattern pattern = Pattern.compile(
				"\\{\\s*" +
						"\"name\":\"(.*?)\"," +
						"\\s*\"passed\":(true|false)," +
						"\\s*\"message\":\"(.*?)\"" +
						"\\s*\\}"
		);

		Matcher matcher = pattern.matcher(json);

		while (matcher.find()) {

			String name = matcher.group(1);

			boolean passedCheck =
					Boolean.parseBoolean(
							matcher.group(2)
					);

			String message = matcher.group(3);

			if (passedCheck) {

				System.out.println(
						GREEN + "[✓] " + name + RESET
				);

			} else {

				System.out.println(
						RED + "[✗] " + name + RESET
				);

				System.out.println(
						RED + "    " + message + RESET
				);
			}
		}

		System.out.println();

		if (failed == 0) {

			System.out.println(
					GREEN +
							BOLD +
							"Alla kontroller är godkända." +
							RESET
			);

		} else {

			System.out.println(
					RED +
							BOLD +
							failed +
							" kontroll(er) behöver åtgärdas." +
							RESET
			);
		}

		System.out.println();
		System.out.println(
				CYAN + "========================================" + RESET
		);
	}
	private static int extractInt(
			String json,
			String key
	) {

		Pattern pattern = Pattern.compile(
				"\"" + key + "\":(\\d+)"
		);

		Matcher matcher = pattern.matcher(json);

		if (!matcher.find()) {
			return 0;
		}

		return Integer.parseInt(
				matcher.group(1)
		);
	}
	private static String extractJsonValue(
			String json,
			String key
	) {

		String search =
				"\"" + key + "\":\"";

		int start =
				json.indexOf(search);

		if (start < 0) {
			return null;
		}

		start += search.length();

		int end =
				json.indexOf("\"", start);

		if (end < 0) {
			return null;
		}

		return json.substring(
				start,
				end
		);
	}

	private static String extractHttpsPublicUrl(String json) {

		String marker = "\"public_url\":\"";

		int start = json.indexOf(marker);

		if (start == -1) {
			return null;
		}

		start += marker.length();

		int end = json.indexOf("\"", start);

		if (end == -1) {
			return null;
		}

		return json.substring(start, end);
	}
}