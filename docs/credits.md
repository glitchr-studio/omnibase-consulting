# Hours paid in advance

The plan of the omnibase family gives hours paid in advance to
omnibase/marketplace's generic `Credit` (with `Entitlement`, `Usage`),
written with the events platform. This bundle writes no hours entity of its
own and copies nothing of forge's `HourCredit`.

What is here:

- `Offering::$hours`: the package an offering would be sold as;
- `Service\CreditBridge`: `isAvailable()` is `class_exists('Base\Marketplace\Entity\Credit')`,
  `canSell($offering)` is that and a package of hours;
- the offering page shows the package only when `canSell()` says so.

When the marketplace has `Credit`, the bridge is where the product is made
and the hours counted down.
