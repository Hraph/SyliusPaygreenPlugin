<?php

namespace Hraph\SyliusPaygreenPlugin\Types;

interface TransferTransitions
{
    public const GRAPH = 'hraph_paygreen_transfer';

    public const TRANSITION_PROCESS = 'process';

    public const TRANSITION_COMPLETE = 'complete';

    public const TRANSITION_FAIL = 'fail';

    public const TRANSITION_CANCEL = 'cancel';
}
