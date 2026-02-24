<?php

declare(strict_types=1);

namespace Sujip\Wise\Resources\Contact;

use Sujip\Wise\Resources\Contact\Models\Contact;
use Sujip\Wise\Resources\Contact\Requests\CreateContactRequest;
use Sujip\Wise\Resources\Resource;

final class ContactResource extends Resource
{
    public function create(int $profileId, CreateContactRequest $request): Contact
    {
        $payload = $this->client->request(
            'POST',
            "/v2/profiles/{$profileId}/contacts",
            query: ['isDirectIdentifierCreation' => 'true'],
            body: $request->toArray(),
        );

        return Contact::fromArray($payload);
    }
}
