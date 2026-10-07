<?php

namespace Base\Consulting\Form\Type;

use Base\Consulting\Entity\Offering;
use Base\Consulting\Form\Model\QuoteRequestModel;
use Base\Consulting\Repository\OfferingRepository;
use Base\Form\Type\ContactType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * The quote request: omnibase's contact form (Base\Form\Type\ContactType:
 * name, e-mail, phone, message) with what a quote needs on top - the
 * offering, the organisation, the participants, the dates, the place, the
 * budget - and the notice to accept. Guarded as glitchr/omnibase guards a
 * form (option `guard`, Base\Service\FormGuard): a trap, the time it takes,
 * the lists, the captcha when glitchr/omniguard has one - in place of the
 * contact form's own `website` trap.
 */
class QuoteRequestType extends AbstractType
{
    public function getParent(): string
    {
        return ContactType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => QuoteRequestModel::class,
            'phone' => true,
            'subject' => false,
            'attachments' => false,
            'buttons' => false,
            'trap' => false,
            'guard' => ['action' => 'quote'],
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('offering', EntityType::class, [
                'class' => Offering::class,
                'required' => false,
                'placeholder' => 'quote.fields.offering_none',
                'query_builder' => static fn (OfferingRepository $r) => $r->createQueryBuilder('o')->where('o.visible = true')->andWhere('o.quotable = true')->orderBy('o.position', 'ASC'),
                'choice_label' => 'title',
                'label' => 'quote.fields.offering', 'translation_domain' => 'consulting',
            ])
            ->add('organisation', TextType::class, ['required' => false, 'label' => 'quote.fields.organisation', 'translation_domain' => 'consulting', 'attr' => ['autocomplete' => 'organization']])
            ->add('participants', TextType::class, ['required' => false, 'label' => 'quote.fields.participants', 'translation_domain' => 'consulting'])
            ->add('dates', TextType::class, ['required' => false, 'label' => 'quote.fields.dates', 'translation_domain' => 'consulting'])
            ->add('location', TextType::class, ['required' => false, 'label' => 'quote.fields.location', 'translation_domain' => 'consulting'])
            ->add('budget', TextType::class, ['required' => false, 'label' => 'quote.fields.budget', 'translation_domain' => 'consulting'])
            ->add('consent', CheckboxType::class, ['required' => true, 'label' => 'quote.fields.consent', 'translation_domain' => 'consulting']);
    }
}
