import { Box, BoxSubtitle, BoxTitle } from '@Atoms/Box';
import FormCheckbox from '@Components/fields/FormCheckbox';
import FormInput from '@Components/fields/FormInput';
import { colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import Show from '@Controls/Show';
import type { Coupon } from '@CouponServices/coupon';
import { requiredRule } from '@Utils/validation';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { Controller, useFormContext } from 'react-hook-form';

function CouponLimitation() {
  const form = useFormContext<Coupon>();
  const usage_limit_status = form.watch('usage_limit_status');
  const per_user_limit_status = form.watch('per_user_limit_status');

  return (
    <Box bordered css={styles.discountWrapper}>
      <div css={styles.couponWrapper}>
        <BoxTitle>{__('Usage Limitation', 'tutor')}</BoxTitle>
      </div>
      <div css={styles.couponWrapper}>
        <div css={styles.limitWrapper}>
          <Controller
            name="usage_limit_status"
            control={form.control}
            render={(controllerProps) => (
              <FormCheckbox
                {...controllerProps}
                label={__('Limit number of times this coupon can be used in total', 'tutor')}
                labelCss={styles.checkBoxLabel}
              />
            )}
          />
          <Show when={usage_limit_status}>
            <Controller
              name="total_usage_limit"
              control={form.control}
              rules={requiredRule()}
              render={(controllerProps) => (
                <div css={styles.limitInput}>
                  <FormInput {...controllerProps} type="number" placeholder={__('0', 'tutor')} />
                </div>
              )}
            />
          </Show>
        </div>
      </div>
      <div css={styles.couponWrapper}>
        <div css={styles.limitWrapper}>
          <Controller
            name="per_user_limit_status"
            control={form.control}
            render={(controllerProps) => (
              <FormCheckbox
                {...controllerProps}
                label={__('Limit number of times this coupon can be used by a customer', 'tutor')}
                labelCss={styles.checkBoxLabel}
              />
            )}
          />
          <Show when={per_user_limit_status}>
            <Controller
              name="per_user_usage_limit"
              control={form.control}
              rules={requiredRule()}
              render={(controllerProps) => (
                <div css={styles.limitInput}>
                  <FormInput {...controllerProps} type="number" placeholder={__('0', 'tutor')} />
                </div>
              )}
            />
          </Show>
        </div>
      </div>
    </Box>
  );
}

export default CouponLimitation;

const styles = {
  discountWrapper: css`
		display: flex;
		flex-direction: column;
		gap: ${spacing[12]};
	`,
  couponWrapper: css`
		display: flex;
		flex-direction: column;
		gap: ${spacing[4]};
	`,
  limitWrapper: css`
		display: flex;
		flex-direction: column;
		gap: ${spacing[8]};
	`,
  checkBoxLabel: css`
		${typography.caption()};
		color: ${colorTokens.text.title};
	`,
  limitInput: css`
		width: fit-content;
		margin-left: ${spacing[28]};
	`,
};
