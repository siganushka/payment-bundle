<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle\Enum;

use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum PaymentState: string implements TranslatableInterface
{
    case Pending = 'pending';
    case Succeed = 'succeed';
    case Failed = 'failed';
    case Cancelled = 'cancelled';

    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return $translator->trans('payment.state.'.$this->value, locale: $locale);
    }

    public function theme(): string
    {
        return match ($this) {
            self::Pending => 'primary',
            self::Succeed => 'success',
            self::Failed => 'danger',
            self::Cancelled => 'secondary',
        };
    }
}
