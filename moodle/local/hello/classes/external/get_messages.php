<?php
declare(strict_types=1);

namespace local_hello\external;

use core_external\external_api;
use core_external\external_description;
use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;
use local_hello\Application\DTO\MessageFilterDTO;
use local_hello\Infrastructure\Factory\MessageServiceFactory;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');

/**
 * Função externa para listar mensagens do plugin local_hello.
 *
 * @author David <github.com/DavidMarquesDev>
 */
class get_messages extends external_api
{
    /**
     * Define os parâmetros aceitos pela função externa.
     *
     * @return \external_function_parameters Estrutura de parâmetros.
     *
     * @example ['userid' => 0]
     *
     * @author David <github.com/DavidMarquesDev>
     */
    public static function execute_parameters(): external_function_parameters
    {
        return new external_function_parameters([
            'userid' => new external_value(PARAM_INT, 'ID do usuário alvo. Use 0 para o usuário autenticado.', VALUE_DEFAULT, 0),
        ]);
    }

    /**
     * Executa a busca de mensagens do usuário informado.
     *
     * Se o usuário alvo não for o autenticado, exige capacidade administrativa
     * para evitar exposição indevida de dados.
     *
     * @param int $userid ID do usuário alvo.
     *
     * @throws \required_capability_exception Quando o usuário não possui permissão para visualizar o recurso.
     * @throws \invalid_parameter_exception Quando os parâmetros não são válidos.
     * @throws \moodle_exception Quando ocorre erro de contexto ou autenticação.
     *
     * @return array<int, array<string, int|string>> Lista de mensagens no formato da API externa.
     *
     * @example get_messages::execute(0)
     *
     * @author David <github.com/DavidMarquesDev>
     */
    public static function execute(int $userid = 0): array
    {
        global $DB, $USER;

        $params = self::validate_parameters(self::execute_parameters(), ['userid' => $userid]);
        $context = \context::instance_by_id(\context_system::instance()->id);

        self::validate_context($context);
        require_capability('local/hello:view', $context);

        $targetuserid = $params['userid'] === 0 ? (int) $USER->id : (int) $params['userid'];
        if ($targetuserid !== (int) $USER->id) {
            require_capability('moodle/site:config', $context);
        }

        $messageservice = MessageServiceFactory::create($DB);
        $listpayload = $messageservice->listMessages($targetuserid, new MessageFilterDTO(0, 1000, '', 'recent'));

        $messages = [];
        foreach ($listpayload['records'] as $record) {
            $messages[] = [
                'id' => (int) $record->id,
                'message' => (string) $record->message,
                'timecreated' => (int) $record->timecreated,
            ];
        }

        return $messages;
    }

    /**
     * Define a estrutura de retorno da função externa.
     *
     * @return \external_description Estrutura de retorno da função.
     *
     * @author David <github.com/DavidMarquesDev>
     */
    public static function execute_returns(): external_description
    {
        return new external_multiple_structure(
            new external_single_structure([
                'id' => new external_value(PARAM_INT, 'ID da mensagem.'),
                'message' => new external_value(PARAM_TEXT, 'Conteúdo da mensagem.'),
                'timecreated' => new external_value(PARAM_INT, 'Timestamp de criação da mensagem.'),
            ])
        );
    }
}
