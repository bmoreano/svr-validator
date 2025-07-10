<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * App\Models\Criterion
 *
 * @property int $id
 * @property string $text
 * @property string $category
 * @property bool $is_active
 * @property int $sort_order
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ValidationResponse> $validationResponses
 * @property-read int|null $validation_responses_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Criterion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Criterion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Criterion query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Criterion whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Criterion whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Criterion whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Criterion whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Criterion whereText($value)
 * @mixin \Eloquent
 */
	class Criterion extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $team_id
 * @property int $user_id
 * @property string|null $role
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Membership newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Membership newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Membership query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Membership whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Membership whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Membership whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Membership whereTeamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Membership whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Membership whereUserId($value)
 * @mixin \Eloquent
 */
	class Membership extends \Eloquent {}
}

namespace App\Models{
/**
 * Representa una opción de respuesta para una pregunta de opción múltiple.
 * 
 * Puede ser la respuesta correcta o un distractor.
 * 
 * App\Models\Option
 *
 * @property int $id
 * @property int $question_id
 * @property string $option_text
 * @property bool $is_correct
 * @property string|null $argumentation
 * @property-read \App\Models\Question $question
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Option newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Option newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Option query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Option whereArgumentation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Option whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Option whereIsCorrect($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Option whereOptionText($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Option whereQuestionId($value)
 * @mixin \Eloquent
 */
	class Option extends \Eloquent {}
}

namespace App\Models{
/**
 * Representa un reactivo o pregunta de opción múltiple en el sistema.
 * 
 * Es la entidad central del proceso de creación y validación.
 * 
 * App\Models\Question
 *
 * @property int $id
 * @property int $author_id
 * @property string $stem
 * @property string $status
 * @property string|null $bibliography
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $author
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Option> $options
 * @property-read int|null $options_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Validation> $validations
 * @property-read int|null $validations_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Question newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Question newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Question query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Question whereAuthorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Question whereBibliography($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Question whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Question whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Question whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Question whereStem($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Question whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property array<array-key, mixed>|null $corregido_administrador
 * @property string|null $comentario_administrador
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Question whereComentarioAdministrador($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Question whereCorregidoAdministrador($value)
 */
	class Question extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property bool $personal_team
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $owner
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TeamInvitation> $teamInvitations
 * @property-read int|null $team_invitations_count
 * @property-read \App\Models\Membership|null $membership
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Database\Factories\TeamFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team wherePersonalTeam($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereUserId($value)
 * @mixin \Eloquent
 */
	class Team extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $team_id
 * @property string $email
 * @property string|null $role
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Team $team
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamInvitation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamInvitation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamInvitation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamInvitation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamInvitation whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamInvitation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamInvitation whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamInvitation whereTeamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamInvitation whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class TeamInvitation extends \Eloquent {}
}

namespace App\Models{
/**
 * Representa un usuario en la aplicación. Puede ser un Autor,
 * Validador o Administrador.
 * 
 * App\Models\User
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property int|null $current_team_id
 * @property string|null $profile_photo_path
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property string|null $two_factor_confirmed_at
 * @property string $role
 * @property-read \App\Models\Team|null $currentTeam
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Team> $ownedTeams
 * @property-read int|null $owned_teams_count
 * @property-read string $profile_photo_url
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Question> $questions
 * @property-read int|null $questions_count
 * @property-read \App\Models\Membership|null $membership
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Team> $teams
 * @property-read int|null $teams_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Validation> $validations
 * @property-read int|null $validations_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCurrentTeamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereProfilePhotoPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTwoFactorConfirmedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTwoFactorRecoveryCodes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTwoFactorSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class User extends \Eloquent {}
}

namespace App\Models{
/**
 * Representa una única sesión de validación para una pregunta,
 * realizada por un validador específico (ya sea humano o IA).
 * 
 * App\Models\Validation
 *
 * @property int $id
 * @property int $question_id
 * @property int $validator_id
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Question $question
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ValidationResponse> $responses
 * @property-read int|null $responses_count
 * @property-read \App\Models\User $validator
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Validation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Validation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Validation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Validation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Validation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Validation whereQuestionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Validation whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Validation whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Validation whereValidatorId($value)
 * @mixin \Eloquent
 */
	class Validation extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $validation_id
 * @property int $criterion_id
 * @property string $response
 * @property string|null $comment
 * @property-read \App\Models\Criterion $criterion
 * @property-read Validation $validation
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ValidationResponse newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ValidationResponse newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ValidationResponse query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ValidationResponse whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ValidationResponse whereCriterionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ValidationResponse whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ValidationResponse whereResponse($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ValidationResponse whereValidationId($value)
 * @mixin \Eloquent
 */
	class ValidationResponse extends \Eloquent {}
}

