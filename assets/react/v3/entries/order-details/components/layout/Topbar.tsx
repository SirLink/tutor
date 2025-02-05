import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';
import Container from '@Components/Container';
import { useModal } from '@Components/modals/Modal';
import { tutorConfig } from '@Config/config';
import { borderRadius, colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import Show from '@Controls/Show';
import CancelOrderModal from '@OrderComponents/modals/CancelOrderModal';
import { OrderBadge } from '@OrderComponents/order/OrderBadge';
import { PaymentBadge } from '@OrderComponents/order/PaymentBadge';
import { useOrderContext } from '@OrderContexts/order-context';
import { styleUtils } from '@Utils/style-utils';
import { css } from '@emotion/react';
import { __, sprintf } from '@wordpress/i18n';

export const TOPBAR_HEIGHT = 96;

function Topbar() {
  const { order } = useOrderContext();

  const { showModal } = useModal();

  function handleGoBack() {
    const urlParams = new URLSearchParams(window.location.search);
    const redirectUrl = urlParams.get('redirect_url');
    if (redirectUrl) {
      window.location.href = decodeURIComponent(redirectUrl);
    } else {
      window.location.href = `${tutorConfig.home_url}/wp-admin/admin.php?page=tutor_orders`;
    }
  }

  return (
    <div css={styles.wrapper}>
      <Container>
        <div css={styles.innerWrapper}>
          <div css={styles.left}>
            <button type="button" css={styleUtils.backButton} onClick={handleGoBack}>
              <SVGIcon name="arrowLeft" width={26} height={26} />
            </button>
            <div>
              <div css={styles.headerContent}>
                <h4 css={typography.heading5('medium')}>{sprintf(__('Order #%s', 'tutor'), order.id)}</h4>
                <Show when={order.payment_status}>
                  <PaymentBadge status={order.payment_status} />
                </Show>
                <Show when={order.order_status}>
                  <OrderBadge status={order.order_status} />
                </Show>
              </div>
              <Show
                when={order.updated_at_readable}
                fallback={
                  <p css={styles.updateMessage}>
                    {sprintf(
                      __('Created by %s at %s', 'tutor'),
                      order.created_by,
                      order.created_at_readable
                    )}
                  </p>
                }
              >
                {(date) => (
                  <p css={styles.updateMessage}>
                    {sprintf(
                      __('Updated by %s at %s', 'tutor'),
                      order.updated_by,
                      date
                    )}
                  </p>
                )}
              </Show>
            </div>
          </div>

          <Show when={order.order_type === 'single_order' && order.order_status !== 'cancelled'}>
            <Button
              variant="tertiary"
              onClick={() => {
                showModal({
                  component: CancelOrderModal,
                  props: {
                    title: sprintf(__('Cancel order #%s', 'tutor'), order.id),
                    order_id: order.id,
                  },
                });
              }}
            >
              {__('Cancel Order', 'tutor')}
            </Button>
          </Show>
        </div>
      </Container>
    </div>
  );
}

export default Topbar;

const styles = {
  wrapper: css`
    height: ${TOPBAR_HEIGHT}px;
    background: ${colorTokens.background.white};
  `,
  innerWrapper: css`
    display: flex;
    align-items: center;
    justify-content: space-between;
    height: 100%;
  `,
  headerContent: css`
    display: flex;
    align-items: center;
    gap: ${spacing[16]};
  `,
  left: css`
    display: flex;
    gap: ${spacing[16]};
  `,
  updateMessage: css`
    ${typography.body()};
    color: ${colorTokens.text.subdued};
  `,
};
