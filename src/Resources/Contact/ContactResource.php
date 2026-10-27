<?php

declare(strict_types=1);

namespace Theocode\Wise\Resources\Contact;

use Theocode\Wise\Resources\Contact\Models\Contact;
use Theocode\Wise\Resources\Contact\Requests\CreateContactRequest;
use Theocode\Wise\Resources\Resource;

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
